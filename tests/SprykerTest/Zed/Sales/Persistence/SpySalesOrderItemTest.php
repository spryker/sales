<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Persistence;

use Codeception\Test\Unit;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateHistory;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Sales
 * @group Persistence
 * @group SpySalesOrderItemTest
 * Add your own group annotations below this line
 */
class SpySalesOrderItemTest extends Unit
{
    /**
     * @return void
     */
    public function testPostSaveShouldNotCreateNewStateMachineHistoryEntryWhenStateNotChanged(): void
    {
        $salesOrderItemEntityMock = $this->createMockedSalesOrderItemEntity();

        $salesOrderItemEntityMock->expects($this->never())->method('createOmsOrderItemStateHistoryEntity');

        $salesOrderItemEntityMock->setCanceledAmount(100);
        $salesOrderItemEntityMock->save();
    }

    /**
     * @return void
     */
    public function testPostSaveShouldCreateStateMachineHistoryEntryWhenStateChanged(): void
    {
        $salesOrderItemEntityMock = $this->createMockedSalesOrderItemEntity();

        $salesOrderItemEntityMock
            ->expects($this->once())
            ->method('createOmsOrderItemStateHistoryEntity')
            ->willReturn($this->createMockedOmsOrderItemSateHistoryEntity());

        $salesOrderItemEntityMock->setFkOmsOrderItemState(1);
        $salesOrderItemEntityMock->save();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Sales\Persistence\SpySalesOrderItem
     */
    protected function createMockedSalesOrderItemEntity(): SpySalesOrderItem
    {
        $salesOrderItemEntityMock = $this->getMockBuilder(SpySalesOrderItem::class)
            ->onlyMethods([
                'createOmsOrderItemStateHistoryEntity',
                'doSave',
            ])
            ->getMock();

        $salesOrderItemEntityMock->method('doSave')
            ->willReturn(1);

        return $salesOrderItemEntityMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateHistory
     */
    protected function createMockedOmsOrderItemSateHistoryEntity(): SpyOmsOrderItemStateHistory
    {
        $mockedOmsOrderItemStateHistory = $this->getMockBuilder(SpyOmsOrderItemStateHistory::class)
            ->onlyMethods(['save'])
            ->getMock();

        $mockedOmsOrderItemStateHistory->method('save')
            ->willReturn(1);

        return $mockedOmsOrderItemStateHistory;
    }
}
