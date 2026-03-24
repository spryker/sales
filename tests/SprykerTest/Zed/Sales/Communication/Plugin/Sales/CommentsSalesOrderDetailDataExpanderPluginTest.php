<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Communication\Plugin\Sales;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Sales\Communication\Plugin\Sales\CommentsSalesOrderDetailDataExpanderPlugin;
use SprykerTest\Zed\Sales\SalesCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Sales
 * @group Communication
 * @group Plugin
 * @group Sales
 * @group CommentsSalesOrderDetailDataExpanderPluginTest
 * Add your own group annotations below this line
 */
class CommentsSalesOrderDetailDataExpanderPluginTest extends Unit
{
    protected const string DEFAULT_OMS_PROCESS_NAME = 'Test01';

    protected SalesCommunicationTester $tester;

    public function testExpandAddsCommentsKey(): void
    {
        // Arrange
        $plugin = new CommentsSalesOrderDetailDataExpanderPlugin();
        $orderTransfer = (new OrderTransfer())->setIdSalesOrder(0);

        // Act
        $result = $plugin->expand($orderTransfer, []);

        // Assert
        $this->assertArrayHasKey('comments', $result);
    }

    public function testExpandAddsEmptyCommentsForOrderWithoutComments(): void
    {
        // Arrange
        $this->tester->configureTestStateMachine([static::DEFAULT_OMS_PROCESS_NAME]);
        $saveOrderTransfer = $this->tester->haveOrder([], static::DEFAULT_OMS_PROCESS_NAME);

        $plugin = new CommentsSalesOrderDetailDataExpanderPlugin();
        $orderTransfer = (new OrderTransfer())->setIdSalesOrder($saveOrderTransfer->getIdSalesOrder());

        // Act
        $result = $plugin->expand($orderTransfer, []);

        // Assert
        $this->assertArrayHasKey('comments', $result);
        $this->assertCount(0, $result['comments']);
    }

    public function testExpandPreservesExistingData(): void
    {
        // Arrange
        $plugin = new CommentsSalesOrderDetailDataExpanderPlugin();
        $orderTransfer = (new OrderTransfer())->setIdSalesOrder(0);
        $existingData = ['someKey' => 'someValue'];

        // Act
        $result = $plugin->expand($orderTransfer, $existingData);

        // Assert
        $this->assertArrayHasKey('someKey', $result);
        $this->assertArrayHasKey('comments', $result);
    }
}
