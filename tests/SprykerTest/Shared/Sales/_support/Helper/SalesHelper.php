<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Shared\Sales\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use Generated\Shared\DataBuilder\OrderBuilder;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Orm\Zed\Country\Persistence\SpyCountry;
use Orm\Zed\Country\Persistence\SpyCountryQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemState;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcess;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcessQuery;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderAddressTableMap;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap;
use Orm\Zed\Sales\Persistence\SpySalesDiscount;
use Orm\Zed\Sales\Persistence\SpySalesExpense;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddress;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderTotals;
use Orm\Zed\Sales\Persistence\SpySalesShipment;
use Spryker\Shared\Shipment\ShipmentConfig;
use SprykerTest\Shared\Shipment\Helper\ShipmentMethodDataHelperTrait;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;

class SalesHelper extends Module
{
    use ShipmentMethodDataHelperTrait;
    use DataCleanupHelperTrait;

    /**
     * @var string
     */
    protected const SHIPMENT_METHOD_NAME_STANDARD = 'Standard';

    /**
     * @var array<int>
     */
    protected array $salesOrderEntityIds = [];

    public function _after(TestInterface $test): void
    {
        parent::_after($test);

        $this->salesOrderEntityIds = [];
    }

    public function createOrder(array $seed = []): int
    {
        $salesOrderBuilder = new OrderBuilder($seed);
        $orderTransfer = $salesOrderBuilder->build();
        $orderTransfer->setIdSalesOrder(null);

        $salesOrderEntity = new SpySalesOrder();
        $salesOrderEntity->fromArray($orderTransfer->toArray());

        if (isset($seed[OrderTransfer::ORDER_REFERENCE])) {
            $salesOrderEntity->setOrderReference($seed[OrderTransfer::ORDER_REFERENCE]);
        }

        if (isset($seed[OrderTransfer::ORDER_REFERENCE]) && isset($this->salesOrderEntityIds[$seed[OrderTransfer::ORDER_REFERENCE]])) {
            return $this->salesOrderEntityIds[$seed[OrderTransfer::ORDER_REFERENCE]];
        }

        $this->addOrderDetails($salesOrderEntity, $orderTransfer);
        $this->addAddresses($salesOrderEntity);

        $salesOrderEntity->save();

        $this->getDataCleanupHelper()->_addCleanup(function () use ($salesOrderEntity): void {
            $salesOrderEntity->delete();
        });

        $this->addOrderTotals($salesOrderEntity, $orderTransfer);

        $idSalesExpense = $this->addExpenses($salesOrderEntity);
        $this->addShipment($salesOrderEntity, $idSalesExpense);

        $this->salesOrderEntityIds[$salesOrderEntity->getOrderReference()] = $salesOrderEntity->getIdSalesOrder();

        return $salesOrderEntity->getIdSalesOrder();
    }

    public function createOrderWithOneItem(): void
    {
        $i = $this;
        $idSalesOrder = $i->createOrder();
        $i->createSalesOrderItemForOrder($idSalesOrder);
    }

    protected function addExpenses(SpySalesOrder $salesOrderEntity, ?OrderTransfer $orderTransfer = null): int
    {
        return $this->addShipmentExpense($salesOrderEntity, $orderTransfer);
    }

    protected function addOrderDetails(SpySalesOrder $salesOrderEntity, ?OrderTransfer $orderTransfer = null): void
    {
        // These are the default data when not changed from outside
        $salesOrderEntity->setOrderReference($salesOrderEntity->getOrderReference() ?? random_int(0, 9999999));
        $salesOrderEntity->setCurrencyIsoCode('EUR');
        $salesOrderEntity->setPriceMode(null);
        $salesOrderEntity->setIsTest(true);
        $salesOrderEntity->setSalutation(SpySalesOrderTableMap::COL_SALUTATION_MR);
        $salesOrderEntity->setFirstName('FirstName');
        $salesOrderEntity->setLastName('LastName');

        if (!$orderTransfer) {
            return;
        }

        $salesOrderEntity->fromArray($orderTransfer->modifiedToArray());
    }

    protected function addAddresses(SpySalesOrder $salesOrderEntity): void
    {
        $billingAddressEntity = $salesOrderEntity->getBillingAddress();

        if ($billingAddressEntity === null) {
            $billingAddressEntity = $this->createBillingAddress();
            $salesOrderEntity->setBillingAddress($billingAddressEntity);
        }

        $shippingAddressEntity = $salesOrderEntity->getShippingAddress();

        if ($shippingAddressEntity !== null) {
            return;
        }

        $salesOrderEntity->setShippingAddress($billingAddressEntity);
    }

    protected function getCountryEntity(): SpyCountry
    {
        $countryQuery = new SpyCountryQuery();
        $countryQuery->filterByIso2Code('DE');
        $countryQuery->filterByIso3Code('DEU');
        $countryQuery->filterByName('Germany');
        $countryQuery->filterByPostalCodeMandatory(true);
        $countryQuery->filterByPostalCodeRegex('\d{5}');

        $countryEntity = $countryQuery->findOneOrCreate();
        $countryEntity->save();

        return $countryEntity;
    }

    protected function addShipment(SpySalesOrder $salesOrderEntity, int $idSalesExpense): void
    {
        $shipmentMethodTransfer = $this->getShipmentMethodDataHelper()->haveShipmentMethod(
            [ShipmentMethodTransfer::NAME => static::SHIPMENT_METHOD_NAME_STANDARD],
        );

        $shipmentMethod = new SpySalesShipment();
        $shipmentMethod->setName($shipmentMethodTransfer->getNameOrFail());
        $shipmentMethod->setCarrierName($shipmentMethodTransfer->getCarrierNameOrFail());
        $shipmentMethod->setFkSalesExpense($idSalesExpense);
        $shipmentMethod->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $shipmentMethod->save();

        $this->getDataCleanupHelper()->_addCleanup(function () use ($shipmentMethod): void {
            $shipmentMethod->delete();
        });

        $salesOrderEntity->addSpySalesShipment($shipmentMethod);
    }

    public function createSalesOrderItemForOrder(int $idSalesOrder, array $salesOrderItem = []): SpySalesOrderItem
    {
        $salesOrderQuery = new SpySalesOrderQuery();
        $salesOrderEntity = $salesOrderQuery->findOneByIdSalesOrder($idSalesOrder);

        $salesOrderItem = $this->createSalesOrderItem($salesOrderItem);
        $salesOrderItem->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $salesOrderItem->save();

        $this->getDataCleanupHelper()->_addCleanup(function () use ($salesOrderItem): void {
            $salesOrderItem->delete();
        });

        return $salesOrderItem;
    }

    protected function createSalesOrderItem(array $salesOrderItem): SpySalesOrderItem
    {
        $salesOrderItemEntity = new SpySalesOrderItem();
        $salesOrderItemEntity->fromArray($salesOrderItem);
        if ($salesOrderItemEntity->getName() === null) {
            $salesOrderItemEntity->setName('name');
        }
        if ($salesOrderItemEntity->getSku() === null) {
            $salesOrderItemEntity->setSku('sku');
        }
        if ($salesOrderItemEntity->getGrossPrice() === null) {
            $salesOrderItemEntity->setGrossPrice(1000);
        }
        if ($salesOrderItemEntity->getTaxRate() === null) {
            $salesOrderItemEntity->setTaxRate(19);
        }
        if ($salesOrderItemEntity->getQuantity() === null) {
            $salesOrderItemEntity->setQuantity(1);
        }
        if ($salesOrderItemEntity->getGroupKey() === null) {
            $salesOrderItemEntity->setGroupKey('key');
        }

        $omsOrderItemStateEntity = $this->getOrderItemState($salesOrderItem);
        $salesOrderItemEntity->setFkOmsOrderItemState($omsOrderItemStateEntity->getIdOmsOrderItemState());

        $omsOrderProcessEntity = $this->getOrderProcess($salesOrderItem);
        $salesOrderItemEntity->setFkOmsOrderProcess($omsOrderProcessEntity->getIdOmsOrderProcess());

        return $salesOrderItemEntity;
    }

    public function createDiscountForSalesOrderItem(int $idSalesOrderItem, array $discount = []): void
    {
        $salesOrderDiscountEntity = new SpySalesDiscount();
        $salesOrderDiscountEntity->fromArray($discount);
        $salesOrderDiscountEntity->setFkSalesOrderItem($idSalesOrderItem);
        if ($salesOrderDiscountEntity->getName() === null) {
            $salesOrderDiscountEntity->setName('discount name');
        }
        if ($salesOrderDiscountEntity->getDisplayName() === null) {
            $salesOrderDiscountEntity->setDisplayName('discount display name');
        }
        if ($salesOrderDiscountEntity->getAmount() === null) {
            $salesOrderDiscountEntity->setAmount(33);
        }

        $salesOrderDiscountEntity->save();
    }

    protected function getOrderItemState(array $salesOrderItem): SpyOmsOrderItemState
    {
        $expectedState = (!empty($salesOrderItem['state'])) ? $salesOrderItem['state'] : 'new';
        $omsOrderItemStateQuery = new SpyOmsOrderItemStateQuery();
        $omsOrderItemStateEntity = $omsOrderItemStateQuery->filterByName($expectedState)->findOneOrCreate();
        $omsOrderItemStateEntity->save();

        return $omsOrderItemStateEntity;
    }

    protected function getOrderProcess(array $salesOrderItem): SpyOmsOrderProcess
    {
        $expectedProcess = (!empty($salesOrderItem['process'])) ? $salesOrderItem['process'] : 'Nopayment01';
        $omsOrderProcessQuery = new SpyOmsOrderProcessQuery();
        $omsOrderProcessEntity = $omsOrderProcessQuery->filterByName($expectedProcess)->findOneOrCreate();
        $omsOrderProcessEntity->save();

        return $omsOrderProcessEntity;
    }

    protected function createBillingAddress(): SpySalesOrderAddress
    {
        $billingAddressEntity = new SpySalesOrderAddress();

        $countryEntity = $this->getCountryEntity();
        $billingAddressEntity->setCountry($countryEntity);

        $billingAddressEntity->setSalutation(SpySalesOrderAddressTableMap::COL_SALUTATION_MR);
        $billingAddressEntity->setFirstName('FirstName');
        $billingAddressEntity->setLastName('LastName');
        $billingAddressEntity->setAddress1('Address1');
        $billingAddressEntity->setAddress2('Address2');
        $billingAddressEntity->setCity('City');
        $billingAddressEntity->setZipCode('12345');
        $billingAddressEntity->save();

        $this->getDataCleanupHelper()->_addCleanup(function () use ($billingAddressEntity): void {
            $billingAddressEntity->delete();
        });

        return $billingAddressEntity;
    }

    protected function addShipmentExpense(SpySalesOrder $salesOrderEntity, ?OrderTransfer $orderTransfer = null): int
    {
        $shipmentExpense = new SpySalesExpense();
        $shipmentExpense->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $shipmentExpense->setName('default');
        $shipmentExpense->setType(ShipmentConfig::SHIPMENT_EXPENSE_TYPE);
        $shipmentExpense->setGrossPrice(100);

        if ($orderTransfer !== null && $this->findShipmentExpense($orderTransfer)) {
            $expenseTransfer = $this->findShipmentExpense($orderTransfer);
            $shipmentExpense->fromArray($expenseTransfer->modifiedToArray());
        }

        $shipmentExpense->save();

        $this->getDataCleanupHelper()->_addCleanup(function () use ($shipmentExpense): void {
            $shipmentExpense->delete();
        });

        return $shipmentExpense->getIdSalesExpense();
    }

    protected function findShipmentExpense(OrderTransfer $orderTransfer): ?ExpenseTransfer
    {
        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            if ($expenseTransfer->getType() !== ShipmentConfig::SHIPMENT_EXPENSE_TYPE) {
                continue;
            }

            return $expenseTransfer;
        }

        return null;
    }

    protected function addOrderTotals(SpySalesOrder $salesOrderEntity, ?OrderTransfer $orderTransfer = null): void
    {
        $salesOrderTotals = new SpySalesOrderTotals();

        // This is the default data when not changed from outside
        $salesOrderTotals->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $salesOrderTotals->setTaxTotal(10);
        $salesOrderTotals->setSubtotal(100);
        $salesOrderTotals->setDiscountTotal(10);
        $salesOrderTotals->setGrandTotal(100);

        // When possible, apply the data from the order transfer
        if ($orderTransfer !== null && $orderTransfer->getTotals()) {
            $salesOrderTotals->fromArray($orderTransfer->getTotals()->modifiedToArray());
        }

        $salesOrderTotals->save();

        $this->getDataCleanupHelper()->_addCleanup(function () use ($salesOrderTotals): void {
            $salesOrderTotals->delete();
        });
    }
}
