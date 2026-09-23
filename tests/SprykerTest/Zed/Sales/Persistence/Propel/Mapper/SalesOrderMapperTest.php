<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Persistence\Propel\Mapper;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemState;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcess;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderMapper;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Sales
 * @group Persistence
 * @group Propel
 * @group Mapper
 * @group SalesOrderMapperTest
 * Add your own group annotations below this line
 */
class SalesOrderMapperTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_ORDER_REFERENCE = 'DE--1234';

    /**
     * @var string
     */
    protected const TEST_SKU = '115_27295368';

    /**
     * @var string
     */
    protected const TEST_STATE_NAME = 'exported';

    /**
     * @var string
     */
    protected const TEST_PROCESS_NAME = 'DummyPayment01';

    public function testMapSalesOrderEntityToSalesOrderTransferMapsItemMoneyColumnsOntoSumProperties(): void
    {
        // Arrange
        $salesOrderEntity = $this->createSalesOrderEntity();

        // Act
        $orderTransfer = (new SalesOrderMapper())->mapSalesOrderEntityToSalesOrderTransfer(
            $salesOrderEntity,
            new OrderTransfer(),
        );

        // Assert
        $itemTransfer = $orderTransfer->getItems()->offsetGet(0);

        $this->assertSame(34500, $itemTransfer->getSumGrossPrice());
        $this->assertSame(30000, $itemTransfer->getSumNetPrice());
        $this->assertSame(34500, $itemTransfer->getSumPrice());
        $this->assertSame(34500, $itemTransfer->getSumSubtotalAggregation());
        $this->assertSame(3450, $itemTransfer->getSumDiscountAmountAggregation());
        $this->assertSame(3450, $itemTransfer->getSumDiscountAmountFullAggregation());
        $this->assertSame(590, $itemTransfer->getSumExpensePriceAggregation());
        $this->assertSame(120, $itemTransfer->getSumProductOptionPriceAggregation());
        $this->assertSame(4500, $itemTransfer->getSumTaxAmount());
        $this->assertSame(4600, $itemTransfer->getSumTaxAmountFullAggregation());
        $this->assertSame(31050, $itemTransfer->getSumPriceToPayAggregation());
    }

    public function testMapSalesOrderEntityToSalesOrderTransferLeavesUnitPricePropertiesUnset(): void
    {
        // Arrange
        $salesOrderEntity = $this->createSalesOrderEntity();

        // Act
        $orderTransfer = (new SalesOrderMapper())->mapSalesOrderEntityToSalesOrderTransfer(
            $salesOrderEntity,
            new OrderTransfer(),
        );

        // Assert
        $itemTransfer = $orderTransfer->getItems()->offsetGet(0);

        $this->assertNull($itemTransfer->getUnitPrice());
        $this->assertNull($itemTransfer->getUnitGrossPrice());
        $this->assertNull($itemTransfer->getUnitNetPrice());
    }

    public function testMapSalesOrderEntityToSalesOrderTransferMapsItemIdentityAndState(): void
    {
        // Arrange
        $salesOrderEntity = $this->createSalesOrderEntity();

        // Act
        $orderTransfer = (new SalesOrderMapper())->mapSalesOrderEntityToSalesOrderTransfer(
            $salesOrderEntity,
            new OrderTransfer(),
        );

        // Assert
        $itemTransfer = $orderTransfer->getItems()->offsetGet(0);

        $this->assertSame(static::TEST_ORDER_REFERENCE, $orderTransfer->getOrderReference());
        $this->assertSame(static::TEST_SKU, $itemTransfer->getSku());
        $this->assertSame(2, $itemTransfer->getQuantity());
        $this->assertSame(static::TEST_STATE_NAME, $itemTransfer->getState()->getName());
        $this->assertSame(static::TEST_PROCESS_NAME, $itemTransfer->getProcess());
    }

    protected function createSalesOrderEntity(): SpySalesOrder
    {
        $salesOrderItemEntity = $this->getMockBuilder(SpySalesOrderItem::class)
            ->onlyMethods(['getState', 'getProcess'])
            ->getMock();
        $salesOrderItemEntity->method('getState')
            ->willReturn((new SpyOmsOrderItemState())->setName(static::TEST_STATE_NAME));
        $salesOrderItemEntity->method('getProcess')
            ->willReturn((new SpyOmsOrderProcess())->setName(static::TEST_PROCESS_NAME));

        $salesOrderItemEntity
            ->setSku(static::TEST_SKU)
            ->setQuantity(2)
            ->setGrossPrice(34500)
            ->setNetPrice(30000)
            ->setPrice(34500)
            ->setSubtotalAggregation(34500)
            ->setDiscountAmountAggregation(3450)
            ->setDiscountAmountFullAggregation(3450)
            ->setExpensePriceAggregation(590)
            ->setProductOptionPriceAggregation(120)
            ->setTaxAmount(4500)
            ->setTaxAmountFullAggregation(4600)
            ->setPriceToPayAggregation(31050);

        $salesOrderEntity = $this->getMockBuilder(SpySalesOrder::class)
            ->onlyMethods(['getItems'])
            ->getMock();
        $salesOrderEntity->setOrderReference(static::TEST_ORDER_REFERENCE);
        $salesOrderEntity->method('getItems')->willReturn(new ObjectCollection([$salesOrderItemEntity]));

        return $salesOrderEntity;
    }
}
