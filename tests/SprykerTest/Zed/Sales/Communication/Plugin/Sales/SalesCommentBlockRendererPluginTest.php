<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Communication\Plugin\Sales;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Sales\Communication\Plugin\Sales\SalesCommentBlockRendererPlugin;
use SprykerTest\Zed\Sales\SalesCommunicationTester;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
 * @group SalesCommentBlockRendererPluginTest
 * Add your own group annotations below this line
 */
class SalesCommentBlockRendererPluginTest extends Unit
{
    protected const string BLOCK_URL = '/sales/comment/add';

    protected const string OTHER_URL = '/other/url';

    protected const string DEFAULT_OMS_PROCESS_NAME = 'Test01';

    protected SalesCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->setDependency(AbstractCommunicationFactory::FORM_FACTORY, $this->getFormFactoryMock());
    }

    public function testIsApplicableReturnsTrueForMatchingUrl(): void
    {
        // Arrange
        $plugin = $this->getBlockRendererPlugin();

        // Act
        $result = $plugin->isApplicable(static::BLOCK_URL);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsApplicableReturnsFalseForNonMatchingUrl(): void
    {
        // Arrange
        $plugin = $this->getBlockRendererPlugin();

        // Act
        $result = $plugin->isApplicable(static::OTHER_URL);

        // Assert
        $this->assertFalse($result);
    }

    public function testGetTemplatePathReturnsExpectedPath(): void
    {
        // Arrange
        $plugin = $this->getBlockRendererPlugin();

        // Act
        $result = $plugin->getTemplatePath(static::BLOCK_URL);

        // Assert
        $this->assertSame('@Sales/Comment/add.twig', $result);
    }

    public function testGetDataReturnsCommentForm(): void
    {
        // Arrange
        $plugin = $this->getBlockRendererPlugin();
        $orderTransfer = (new OrderTransfer())->setIdSalesOrder(null);

        // Act
        $result = $plugin->getData(new Request(), $orderTransfer, static::BLOCK_URL);

        // Assert
        $this->assertArrayHasKey('form', $result);
    }

    public function testGetDataReturnsCommentFormForExistingOrder(): void
    {
        // Arrange
        $this->tester->configureTestStateMachine([static::DEFAULT_OMS_PROCESS_NAME]);
        $saveOrderTransfer = $this->tester->haveOrder([], static::DEFAULT_OMS_PROCESS_NAME);

        $plugin = $this->getBlockRendererPlugin();
        $orderTransfer = (new OrderTransfer())->setIdSalesOrder($saveOrderTransfer->getIdSalesOrder());

        // Act
        $result = $plugin->getData(new Request(), $orderTransfer, static::BLOCK_URL);

        // Assert
        $this->assertArrayHasKey('form', $result);
        $this->assertInstanceOf(FormView::class, $result['form']);
    }

    public function getBlockRendererPlugin(): SalesCommentBlockRendererPlugin
    {
        return new SalesCommentBlockRendererPlugin();
    }

    protected function getFormFactoryMock(): FormFactoryInterface
    {
        $formFactoryMock = $this->getMockBuilder(FormFactoryInterface::class)->getMock();
        $formFactoryMock->method('create')->willReturn($this->getFormMock());
        $formFactoryMock->method('createNamed')->willReturn($this->getFormMock());

        return $formFactoryMock;
    }

    protected function getFormMock(): FormInterface
    {
        $formMock = $this->getMockBuilder(FormInterface::class)->getMock();
        $formMock->method('createView')->willReturn(new FormView());

        return $formMock;
    }
}
