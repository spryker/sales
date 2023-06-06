<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Sales\Helper;

use Codeception\Module;
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
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery;
use Spryker\Shared\Shipment\ShipmentConfig;
use SprykerTest\Shared\Shipment\Helper\ShipmentMethodDataHelperTrait;

class SalesHelper extends Module
{
    use ShipmentMethodDataHelperTrait;

    /**
     * @var string
     */
    protected const SHIPMENT_METHOD_NAME_STANDARD = 'Standard';

    /**
     * @return int
     */
    public function createOrder(): int
    {
        $salesOrderEntity = new SpySalesOrder();

        $this->addOrderDetails($salesOrderEntity);
        $this->addAddresses($salesOrderEntity);
        $salesOrderEntity->save();

        $this->addOrderTotals($salesOrderEntity);

        $idSalesExpense = $this->addExpenses($salesOrderEntity);
        $this->addShipment($salesOrderEntity, $idSalesExpense);

        return $salesOrderEntity->getIdSalesOrder();
    }

    /**
     * @return void
     */
    public function createOrderWithOneItem(): void
    {
        $i = $this;
        $idSalesOrder = $i->createOrder();
        $i->createSalesOrderItemForOrder($idSalesOrder);
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     *
     * @return int
     */
    protected function addExpenses(SpySalesOrder $salesOrderEntity): int
    {
        return $this->addShipmentExpense($salesOrderEntity);
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     *
     * @return void
     */
    protected function addOrderDetails(SpySalesOrder $salesOrderEntity): void
    {
        $salesOrderEntity->setOrderReference(random_int(0, 9999999));
        $salesOrderEntity->setCurrencyIsoCode('EUR');
        $salesOrderEntity->setPriceMode(null);
        $salesOrderEntity->setIsTest(true);
        $salesOrderEntity->setSalutation(SpySalesOrderTableMap::COL_SALUTATION_MR);
        $salesOrderEntity->setFirstName('FirstName');
        $salesOrderEntity->setLastName('LastName');
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     *
     * @return void
     */
    protected function addAddresses(SpySalesOrder $salesOrderEntity): void
    {
        $billingAddressEntity = $salesOrderEntity->getBillingAddress();
        if ($billingAddressEntity === null) {
            $billingAddressEntity = $this->createBillingAddress();
            $salesOrderEntity->setBillingAddress($billingAddressEntity);
        }

        $shippingAddressEntity = $salesOrderEntity->getShippingAddress();
        if ($shippingAddressEntity === null) {
            $salesOrderEntity->setShippingAddress($billingAddressEntity);
        }
    }

    /**
     * @return \Orm\Zed\Country\Persistence\SpyCountry
     */
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

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     * @param int $idSalesExpense
     *
     * @return void
     */
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

        $salesOrderEntity->addSpySalesShipment($shipmentMethod);
    }

    /**
     * @param int $idSalesOrder
     * @param array $salesOrderItem
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItem
     */
    public function createSalesOrderItemForOrder(int $idSalesOrder, array $salesOrderItem = []): SpySalesOrderItem
    {
        $salesOrderQuery = new SpySalesOrderQuery();
        $salesOrderEntity = $salesOrderQuery->findOneByIdSalesOrder($idSalesOrder);

        $salesOrderItem = $this->createSalesOrderItem($salesOrderItem);
        $salesOrderItem->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $salesOrderItem->save();

        return $salesOrderItem;
    }

    /**
     * @param array $salesOrderItem
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItem
     */
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

    /**
     * @param int $idSalesOrderItem
     * @param array $discount
     *
     * @return void
     */
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

    /**
     * @param array $salesOrderItem
     *
     * @return \Orm\Zed\Oms\Persistence\SpyOmsOrderItemState
     */
    protected function getOrderItemState(array $salesOrderItem): SpyOmsOrderItemState
    {
        $expectedState = (!empty($salesOrderItem['state'])) ? $salesOrderItem['state'] : 'new';
        $omsOrderItemStateQuery = new SpyOmsOrderItemStateQuery();
        $omsOrderItemStateEntity = $omsOrderItemStateQuery->filterByName($expectedState)->findOneOrCreate();
        $omsOrderItemStateEntity->save();

        return $omsOrderItemStateEntity;
    }

    /**
     * @param array $salesOrderItem
     *
     * @return \Orm\Zed\Oms\Persistence\SpyOmsOrderProcess
     */
    protected function getOrderProcess(array $salesOrderItem): SpyOmsOrderProcess
    {
        $expectedProcess = (!empty($salesOrderItem['process'])) ? $salesOrderItem['process'] : 'Nopayment01';
        $omsOrderProcessQuery = new SpyOmsOrderProcessQuery();
        $omsOrderProcessEntity = $omsOrderProcessQuery->filterByName($expectedProcess)->findOneOrCreate();
        $omsOrderProcessEntity->save();

        return $omsOrderProcessEntity;
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderAddress
     */
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

        return $billingAddressEntity;
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     *
     * @return int
     */
    protected function addShipmentExpense(SpySalesOrder $salesOrderEntity): int
    {
        $shipmentExpense = new SpySalesExpense();
        $shipmentExpense->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $shipmentExpense->setName('default');
        $shipmentExpense->setType(ShipmentConfig::SHIPMENT_EXPENSE_TYPE);
        $shipmentExpense->setGrossPrice(100);
        $shipmentExpense->save();

        return $shipmentExpense->getIdSalesExpense();
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     *
     * @return void
     */
    protected function addOrderTotals(SpySalesOrder $salesOrderEntity): void
    {
        $salesOrderTotals = new SpySalesOrderTotals();

        $salesOrderTotals->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $salesOrderTotals->setTaxTotal(10);
        $salesOrderTotals->setSubtotal(100);
        $salesOrderTotals->setDiscountTotal(10);
        $salesOrderTotals->setGrandTotal(100);

        $salesOrderTotals->save();
    }
}
