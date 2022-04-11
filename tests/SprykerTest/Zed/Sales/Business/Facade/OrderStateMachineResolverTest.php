<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Business\Facade;

use BadMethodCallException;
use Codeception\TestCase\Test;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Sales\Business\SalesBusinessFactory;
use Spryker\Zed\Sales\SalesConfig;
use Spryker\Zed\Setup\Business\SetupBusinessFactory;
use Spryker\Zed\Sales\Business\StateMachineResolver\OrderStateMachineResolver;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Sales
 * @group Business
 * @group Facade
 * @group OrderStateMachineResolverTest
 * Add your own group annotations below this line
 */
class OrderStateMachineResolverTest extends Test
{
    /**
     * @return void
     */
    public function testResolveReturnsStateMachineProccessNameWhenRequestIsSuccessful(): void
    {
        // Arrange
        $quoteTransfer = $this->createQuoteTransfer('foreignPayments');
        $itemTransfer = new ItemTransfer();
        $salesConfigMock = $this->createSalesConfigMock(true);

        // Act
        $paymentMethodStatemachine = $this->createOrderStateMachineResolverMock($salesConfigMock)->resolve(
            $quoteTransfer,
            $itemTransfer,
        );

        // Assert
        $this->assertEquals('B2CStateMachine01', $paymentMethodStatemachine);
    }

    /**
     * @return void
     */
    public function testResolveThrowsExceptionWhenRequestIsNotSuccessful(): void
    {
        // Arrange
        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();
        $salesConfigMock = $this->createSalesConfigMock(false);

        // Assert
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('You need to provide at least one state machine process for given method!');

        // Act
        $paymentMethodStatemachine = $this->createOrderStateMachineResolverMock($salesConfigMock)->resolve(
            $quoteTransfer,
            $itemTransfer,
        );
    }

    /**
     * @return void
     */
    public function testResolveThrowsExceptionWhenStateMachineIsNotFound(): void
    {
        // Arrange
        $quoteTransfer = $this->createQuoteTransfer('test');
        $itemTransfer = new ItemTransfer();
        $salesConfigMock = $this->createSalesConfigMock(true);

        // Assert
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('You need to provide at least one state machine process for given method!');

        // Act
        $paymentMethodStatemachine = $this->createOrderStateMachineResolverMock($salesConfigMock)->resolve(
            $quoteTransfer,
            $itemTransfer,
        );
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Sales\SalesConfig $salesConfigMock
     *
     * @return \Spryker\Zed\Sales\Business\StateMachineResolver\OrderStateMachineResolver
     */
    protected function createOrderStateMachineResolverMock(SalesConfig $salesConfigMock)
    {
        return new OrderStateMachineResolver($salesConfigMock);
    }

    /**
     * @param bool $isDefaultProcessAllowed
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Sales\SalesConfig
     */
    protected function createSalesConfigMock(bool $isDefaultProcessAllowed): SalesConfig
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Sales\SalesConfig $salesConfigMock */
        $salesConfigMock = $this
            ->getMockBuilder(SalesConfig::class)
            ->setMethods(['isExtendedDeterminationForOrderItemProcessEnabled'])
            ->getMock();

        $salesConfigMock
            ->method('isExtendedDeterminationForOrderItemProcessEnabled')
            ->willReturn( $isDefaultProcessAllowed);

        return $salesConfigMock;
    }

    /**
     * @param string $paymentSelection
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransfer(string $paymentSelection): QuoteTransfer
    {
        $quoteTransfer = new QuoteTransfer();
        $paymentTransfer = new PaymentTransfer();
        $paymentTransfer->setPaymentSelection($paymentSelection);
        $quoteTransfer->setPayment($paymentTransfer);

        return $quoteTransfer;
    }
}
