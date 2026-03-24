<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Communication\Plugin\Sales;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Sales\Communication\Plugin\Sales\OrderTransferBlockRendererPlugin;
use SprykerTest\Zed\Sales\SalesCommunicationTester;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Sales
 * @group Communication
 * @group Plugin
 * @group Sales
 * @group OrderTransferBlockRendererPluginTest
 * Add your own group annotations below this line
 */
class OrderTransferBlockRendererPluginTest extends Unit
{
    protected const string BLOCK_URL = '/sales/order-transfer/view';

    protected SalesCommunicationTester $tester;

    public function testIsApplicableReturnsFalseWhenUrlNotInConfigMap(): void
    {
        // Arrange
        $plugin = $this->getBlockRendererPlugin();

        // Act
        $result = $plugin->isApplicable(static::BLOCK_URL);

        // Assert
        $this->assertFalse($result);
    }

    public function testGetDataReturnsOrderTransfer(): void
    {
        // Arrange
        $plugin = $this->getBlockRendererPlugin();
        $orderTransfer = new OrderTransfer();

        // Act
        $result = $plugin->getData(new Request(), $orderTransfer, static::BLOCK_URL);

        // Assert
        $this->assertArrayHasKey('order', $result);
        $this->assertSame($orderTransfer, $result['order']);
    }

    public function getBlockRendererPlugin(): OrderTransferBlockRendererPlugin
    {
        return new OrderTransferBlockRendererPlugin();
    }
}
