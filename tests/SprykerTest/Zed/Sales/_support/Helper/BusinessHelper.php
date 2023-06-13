<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Helper;

use Codeception\Module;
use DateTime;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\Transfer\ItemTransfer;
use Orm\Zed\Customer\Persistence\SpyCustomer;
use Orm\Zed\Customer\Persistence\SpyCustomerQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemState;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcess;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcessQuery;
use Orm\Zed\Sales\Persistence\SpySalesExpense;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddress;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Orm\Zed\Sales\Persistence\SpySalesOrderTotals;
use Orm\Zed\Sales\Persistence\SpySalesShipment;

class BusinessHelper extends Module
{
    /**
     * @var string
     */
    public const DEFAULT_OMS_PROCESS_NAME = 'Test01';

    /**
     * @var string
     */
    public const DEFAULT_ITEM_STATE = 'test';

    /**
     * @var int
     */
    protected const ORDER_ITEM_QTY = 1;

    /**
     * @var int
     */
    protected const ORDER_ITEM_GROSS_PRICE_1 = 500;

    /**
     * @var int
     */
    protected const ORDER_ITEM_GROSS_PRICE_2 = 800;

    /**
     * @var int
     */
    protected const ORDER_ITEM_TAX_RATE = 19;

    /**
     * @deprecated Use {@link haveSalesOrderEntity()} instead.
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrder
     */
    public function create(): SpySalesOrder
    {
        return $this->haveSalesOrderEntity();
    }

    /**
     * @param iterable|array $itemTransfers
     * @param array $salesOrderOverride
     * @param string|null $omsStateName
     * @param string|null $omsProcessName
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrder
     */
    public function haveSalesOrderEntity(
        iterable $itemTransfers = [],
        array $salesOrderOverride = [],
        ?string $omsStateName = self::DEFAULT_ITEM_STATE,
        ?string $omsProcessName = self::DEFAULT_OMS_PROCESS_NAME
    ): SpySalesOrder {
        $salesOrderAddressEntity = $this->createSalesOrderAddress();
        $omsStateEntity = $this->createOmsState($omsStateName);
        $omsProcessEntity = $this->createOmsProcess($omsProcessName);
        $salesOrderEntity = $this->createSpySalesOrderEntity($salesOrderAddressEntity, $salesOrderOverride);
        $salesExpenseEntity = $this->createSalesExpense($salesOrderEntity);

        $this->createOrderItems(
            $omsStateEntity,
            $salesOrderEntity,
            $omsProcessEntity,
            $itemTransfers,
        );

        $this->createSpySalesShipment($salesOrderEntity->getIdSalesOrder(), $salesExpenseEntity->getIdSalesExpense());
        $this->createOrderTotals($salesOrderEntity->getIdSalesOrder());

        return $salesOrderEntity;
    }

    /**
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderItemState $omsStateEntity
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderProcess $omsOrderProcessEntity
     * @param iterable|array $itemTransfers
     *
     * @return iterable<\Orm\Zed\Sales\Persistence\SpySalesOrderItem>
     */
    protected function createOrderItems(
        SpyOmsOrderItemState $omsStateEntity,
        SpySalesOrder $salesOrderEntity,
        SpyOmsOrderProcess $omsOrderProcessEntity,
        iterable $itemTransfers = []
    ): iterable {
        if (count($itemTransfers) === 0) {
            return $this->createOrderItemsWithDefaultValues($omsStateEntity, $salesOrderEntity, $omsOrderProcessEntity);
        }

        return $this->createOrderItemsUsingItemTransfers(
            $omsStateEntity,
            $salesOrderEntity,
            $omsOrderProcessEntity,
            $itemTransfers,
        );
    }

    /**
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderItemState $omsStateEntity
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderProcess $omsOrderProcessEntity
     * @param iterable $itemTransfers
     *
     * @return iterable<\Orm\Zed\Sales\Persistence\SpySalesOrderItem>
     */
    protected function createOrderItemsUsingItemTransfers(
        SpyOmsOrderItemState $omsStateEntity,
        SpySalesOrder $salesOrderEntity,
        SpyOmsOrderProcess $omsOrderProcessEntity,
        iterable $itemTransfers
    ): iterable {
        $salesOrderItems = [];

        foreach ($itemTransfers as $itemTransfer) {
            $this->createOrderItem(
                $omsStateEntity,
                $salesOrderEntity,
                $omsOrderProcessEntity,
                $itemTransfer,
                static::ORDER_ITEM_QTY,
                static::ORDER_ITEM_GROSS_PRICE_1,
                static::ORDER_ITEM_TAX_RATE,
            );
        }

        return $salesOrderItems;
    }

    /**
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderItemState $omsStateEntity
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderProcess $omsOrderProcessEntity
     *
     * @return iterable<\Orm\Zed\Sales\Persistence\SpySalesOrderItem>
     */
    protected function createOrderItemsWithDefaultValues(
        SpyOmsOrderItemState $omsStateEntity,
        SpySalesOrder $salesOrderEntity,
        SpyOmsOrderProcess $omsOrderProcessEntity
    ): iterable {
        $salesOrderItems = [];

        $salesOrderItems[] = $this->createOrderItem(
            $omsStateEntity,
            $salesOrderEntity,
            $omsOrderProcessEntity,
            (new ItemBuilder())->build(),
            static::ORDER_ITEM_QTY,
            static::ORDER_ITEM_GROSS_PRICE_1,
            static::ORDER_ITEM_TAX_RATE,
        );

        $salesOrderItems[] = $this->createOrderItem(
            $omsStateEntity,
            $salesOrderEntity,
            $omsOrderProcessEntity,
            (new ItemBuilder())->build(),
            static::ORDER_ITEM_QTY,
            static::ORDER_ITEM_GROSS_PRICE_2,
            static::ORDER_ITEM_TAX_RATE,
        );

        return $salesOrderItems;
    }

    /**
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderItemState $omsStateEntity
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderProcess $omsOrderProcessEntity
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param int $quantity
     * @param int $grossPrice
     * @param int $taxRate
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItem
     */
    protected function createOrderItem(
        SpyOmsOrderItemState $omsStateEntity,
        SpySalesOrder $salesOrderEntity,
        SpyOmsOrderProcess $omsOrderProcessEntity,
        ItemTransfer $itemTransfer,
        int $quantity,
        int $grossPrice,
        int $taxRate
    ): SpySalesOrderItem {
        $salesOrderItem = new SpySalesOrderItem();
        $salesOrderItem->setGrossPrice($grossPrice);
        $salesOrderItem->setQuantity($quantity);
        $salesOrderItem->setSku($itemTransfer->getSku());
        $salesOrderItem->setName($itemTransfer->getName());
        $salesOrderItem->setTaxRate($taxRate);
        $salesOrderItem->setFkOmsOrderItemState($omsStateEntity->getIdOmsOrderItemState());
        $salesOrderItem->setProcess($omsOrderProcessEntity);
        $salesOrderItem->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $salesOrderItem->setGroupKey($itemTransfer->getGroupKey());
        $salesOrderItem->save();

        return $salesOrderItem;
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderAddress $salesOrderAddressEntity
     * @param array $salesOrderOverride
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrder
     */
    protected function createSpySalesOrderEntity(SpySalesOrderAddress $salesOrderAddressEntity, array $salesOrderOverride): SpySalesOrder
    {
        $customerEntity = $this->createCustomer();

        $salesOrderEntity = new SpySalesOrder();
        $salesOrderEntity->fromArray($salesOrderOverride);
        $salesOrderEntity->setCustomer($customerEntity);
        $salesOrderEntity->setBillingAddress($salesOrderAddressEntity);
        $salesOrderEntity->setShippingAddress(clone $salesOrderAddressEntity);
        $salesOrderEntity->setOrderReference(md5(time() + rand()));
        $salesOrderEntity->save();

        return $salesOrderEntity;
    }

    /**
     * @param int $idSalesOrder
     * @param int $idSalesExpense
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesShipment
     */
    protected function createSpySalesShipment(int $idSalesOrder, int $idSalesExpense): SpySalesShipment
    {
        $salesShipmentEntity = new SpySalesShipment();
        $salesShipmentEntity->setDeliveryTime('1 h');
        $salesShipmentEntity->setCarrierName('Carrier name');
        $salesShipmentEntity->setName('Shipment name');
        $salesShipmentEntity->setFkSalesOrder($idSalesOrder);
        $salesShipmentEntity->setFkSalesExpense($idSalesExpense);

        $salesShipmentEntity->save();

        return $salesShipmentEntity;
    }

    /**
     * @return \Orm\Zed\Customer\Persistence\SpyCustomer
     */
    protected function createCustomer(): SpyCustomer
    {
        $customerEntity = (new SpyCustomerQuery())
            ->filterByEmail('email@email.tld')
            ->filterByCustomerReference('testing-customer')
            ->findOneOrCreate();

        $customerEntity->setFirstName('First')
            ->setLastName('Last')
            ->setCompany('Company')
            ->save();

        return $customerEntity;
    }

    /**
     * @param string $omsStateName
     *
     * @return \Orm\Zed\Oms\Persistence\SpyOmsOrderItemState
     */
    protected function createOmsState(string $omsStateName): SpyOmsOrderItemState
    {
        $omsStateEntity = (new SpyOmsOrderItemStateQuery())
            ->filterByName($omsStateName)
            ->findOneOrCreate();

        $omsStateEntity->save();

        return $omsStateEntity;
    }

    /**
     * @param string $omsProcessName
     *
     * @return \Orm\Zed\Oms\Persistence\SpyOmsOrderProcess
     */
    protected function createOmsProcess(string $omsProcessName): SpyOmsOrderProcess
    {
        $omsProcessEntity = (new SpyOmsOrderProcessQuery())
            ->filterByName($omsProcessName)
            ->findOneOrCreate();

        $omsProcessEntity->save();

        return $omsProcessEntity;
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesExpense
     */
    protected function createSalesExpense(SpySalesOrder $salesOrderEntity): SpySalesExpense
    {
        $salesExpenseEntity = new SpySalesExpense();
        $salesExpenseEntity->setName('shipping test');
        $salesExpenseEntity->setTaxRate(19);
        $salesExpenseEntity->setGrossPrice(100);
        $salesExpenseEntity->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $salesExpenseEntity->save();

        return $salesExpenseEntity;
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderAddress
     */
    protected function createSalesOrderAddress(): SpySalesOrderAddress
    {
        $salesOrderAddressEntity = new SpySalesOrderAddress();
        $salesOrderAddressEntity->setAddress1(1);
        $salesOrderAddressEntity->setAddress2(2);
        $salesOrderAddressEntity->setSalutation('Mr');
        $salesOrderAddressEntity->setCellPhone('123456789');
        $salesOrderAddressEntity->setCity('City');
        $salesOrderAddressEntity->setCreatedAt(new DateTime());
        $salesOrderAddressEntity->setUpdatedAt(new DateTime());
        $salesOrderAddressEntity->setComment('Comment');
        $salesOrderAddressEntity->setDescription('Description');
        $salesOrderAddressEntity->setCompany('Company');
        $salesOrderAddressEntity->setFirstName('FirstName');
        $salesOrderAddressEntity->setLastName('LastName');
        $salesOrderAddressEntity->setFkCountry(1);
        $salesOrderAddressEntity->setEmail('Email');
        $salesOrderAddressEntity->setZipCode(12345);
        $salesOrderAddressEntity->save();

        return $salesOrderAddressEntity;
    }

    /**
     * @param int $idSalesOrder
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderTotals
     */
    protected function createOrderTotals(int $idSalesOrder): SpySalesOrderTotals
    {
        $salesOrderTotalsEntity = new SpySalesOrderTotals();
        $salesOrderTotalsEntity->setSubtotal(1000);
        $salesOrderTotalsEntity->setGrandTotal(2500);
        $salesOrderTotalsEntity->setFkSalesOrder($idSalesOrder);

        $salesOrderTotalsEntity->save();

        return $salesOrderTotalsEntity;
    }
}
