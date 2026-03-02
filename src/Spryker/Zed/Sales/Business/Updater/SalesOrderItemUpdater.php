<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Updater;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SalesOrderItemCollectionRequestTransfer;
use Generated\Shared\Transfer\SalesOrderItemCollectionResponseTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\Sales\Business\ItemSaver\OrderItemsSaverInterface;
use Spryker\Zed\Sales\Business\StateMachineResolver\OrderStateMachineResolverInterface;
use Spryker\Zed\Sales\Business\Validator\SalesOrderItemValidatorInterface;
use Spryker\Zed\Sales\Persistence\SalesEntityManagerInterface;

class SalesOrderItemUpdater implements SalesOrderItemUpdaterInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Sales\Business\StateMachineResolver\OrderStateMachineResolverInterface
     */
    protected OrderStateMachineResolverInterface $orderStateMachineResolver;

    /**
     * @param \Spryker\Zed\Sales\Persistence\SalesEntityManagerInterface $salesEntityManager
     * @param \Spryker\Zed\Sales\Business\Validator\SalesOrderItemValidatorInterface $salesOrderItemValidator
     * @param \Spryker\Zed\Sales\Business\ItemSaver\OrderItemsSaverInterface $orderItemsSaver
     * @param list<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesOrderItemsPreUpdatePluginInterface> $salesOrderItemsPreUpdatePlugins
     * @param list<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesOrderItemCollectionPostUpdatePluginInterface> $salesOrderItemCollectionPostUpdatePlugins
     */
    public function __construct(
        protected SalesEntityManagerInterface $salesEntityManager,
        protected SalesOrderItemValidatorInterface $salesOrderItemValidator,
        protected OrderItemsSaverInterface $orderItemsSaver,
        protected array $salesOrderItemsPreUpdatePlugins,
        protected array $salesOrderItemCollectionPostUpdatePlugins
    ) {
    }

    public function updateSalesOrderItemCollectionByQuote(
        SalesOrderItemCollectionRequestTransfer $salesOrderItemCollectionRequestTransfer
    ): SalesOrderItemCollectionResponseTransfer {
        $this->assertRequiredFields($salesOrderItemCollectionRequestTransfer);
        $salesOrderItemCollectionResponseTransfer = (new SalesOrderItemCollectionResponseTransfer())
            ->setItems($salesOrderItemCollectionRequestTransfer->getItems());

        $salesOrderItemCollectionResponseTransfer = $this->salesOrderItemValidator->validate($salesOrderItemCollectionResponseTransfer);

        if ($salesOrderItemCollectionResponseTransfer->getErrors()->count()) {
            return $salesOrderItemCollectionResponseTransfer;
        }

        return $this->getTransactionHandler()->handleTransaction(function () use ($salesOrderItemCollectionRequestTransfer): SalesOrderItemCollectionResponseTransfer {
            return $this->executeUpdateSalesOrderItemCollectionByQuoteTransaction($salesOrderItemCollectionRequestTransfer);
        });
    }

    protected function executeUpdateSalesOrderItemCollectionByQuoteTransaction(
        SalesOrderItemCollectionRequestTransfer $salesOrderItemCollectionRequestTransfer
    ): SalesOrderItemCollectionResponseTransfer {
        $quoteTransfer = $salesOrderItemCollectionRequestTransfer->getQuoteOrFail();
        $quoteTransfer->setItems($salesOrderItemCollectionRequestTransfer->getItems());

        $saveOrderTransfer = (new SaveOrderTransfer())
            ->setIdSalesOrder($quoteTransfer->getItems()->offsetGet(0)->getFkSalesOrderOrFail());

        $quoteTransfer = $this->executeSalesOrderItemsPreUpdatePlugins($quoteTransfer, $saveOrderTransfer);

        $itemTransfers = $this->orderItemsSaver->saveOrderItems($quoteTransfer, $saveOrderTransfer, true)->getOrderItems();
        $salesOrderItemCollectionResponseTransfer = (new SalesOrderItemCollectionResponseTransfer())
            ->setItems($itemTransfers);

        return $this->executeSalesOrderItemCollectionPostUpdatePlugins($salesOrderItemCollectionResponseTransfer);
    }

    protected function executeSalesOrderItemsPreUpdatePlugins(
        QuoteTransfer $quoteTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): QuoteTransfer {
        foreach ($this->salesOrderItemsPreUpdatePlugins as $salesOrderItemsPreUpdatePlugin) {
            $quoteTransfer = $salesOrderItemsPreUpdatePlugin->preUpdate($quoteTransfer, $saveOrderTransfer);
        }

        return $quoteTransfer;
    }

    protected function executeSalesOrderItemCollectionPostUpdatePlugins(
        SalesOrderItemCollectionResponseTransfer $salesOrderItemCollectionResponseTransfer
    ): SalesOrderItemCollectionResponseTransfer {
        foreach ($this->salesOrderItemCollectionPostUpdatePlugins as $salesOrderItemCollectionPostUpdatePlugin) {
            $salesOrderItemCollectionResponseTransfer = $salesOrderItemCollectionPostUpdatePlugin->postUpdate(
                $salesOrderItemCollectionResponseTransfer,
            );
        }

        return $salesOrderItemCollectionResponseTransfer;
    }

    protected function assertRequiredFields(
        SalesOrderItemCollectionRequestTransfer $salesOrderItemCollectionRequestTransfer
    ): void {
        $salesOrderItemCollectionRequestTransfer->requireQuote();
        foreach ($salesOrderItemCollectionRequestTransfer->getItems() as $itemTransfer) {
            $itemTransfer
                ->requireFkSalesOrder()
                ->requireIdSalesOrderItem();
        }
    }
}
