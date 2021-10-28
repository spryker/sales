<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Reader;

use Generated\Shared\Transfer\ItemCollectionTransfer;
use Generated\Shared\Transfer\OrderFilterTransfer;
use Generated\Shared\Transfer\OrderItemFilterTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Sales\Persistence\SalesRepositoryInterface;

class OrderReader implements OrderReaderInterface
{
    /**
     * @var \Spryker\Zed\Sales\Business\Reader\OrderItemReaderInterface
     */
    protected $orderItemReader;

    /**
     * @var \Spryker\Zed\Sales\Persistence\SalesRepositoryInterface
     */
    protected $salesRepository;

    /**
     * @var array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderExpanderPluginInterface>
     */
    protected $hydrateOrderPlugins;

    /**
     * @param \Spryker\Zed\Sales\Business\Reader\OrderItemReaderInterface $orderItemReader
     * @param \Spryker\Zed\Sales\Persistence\SalesRepositoryInterface $salesRepository
     * @param array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderExpanderPluginInterface> $hydrateOrderPlugins
     */
    public function __construct(
        OrderItemReaderInterface $orderItemReader,
        SalesRepositoryInterface $salesRepository,
        array $hydrateOrderPlugins = []
    ) {
        $this->orderItemReader = $orderItemReader;
        $this->salesRepository = $salesRepository;
        $this->hydrateOrderPlugins = $hydrateOrderPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderFilterTransfer $orderFilterTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function getOrderTransfer(OrderFilterTransfer $orderFilterTransfer): OrderTransfer
    {
        $orderTransfer = $this->salesRepository->getSalesOrderDetails($orderFilterTransfer);

        $itemCollectionTransfer = $this->getOrderItemsCollectionTransfer($orderTransfer);
        $orderTransfer->setItems($itemCollectionTransfer->getItems());

        $orderTransfer = $this->expandOrderTransferWithOrderTotals($orderTransfer);
        $orderTransfer = $this->expandOrderTransferWithUniqueProductsQuantity($orderTransfer);
        $orderTransfer = $this->executeHydrateOrderPlugins($orderTransfer);

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\ItemCollectionTransfer
     */
    protected function getOrderItemsCollectionTransfer(OrderTransfer $orderTransfer): ItemCollectionTransfer
    {
        $orderItemFilterTransfer = (new OrderItemFilterTransfer())
            ->addOrderReference($orderTransfer->getOrderReference());

        return $this->orderItemReader->getOrderItems($orderItemFilterTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function expandOrderTransferWithOrderTotals(OrderTransfer $orderTransfer): OrderTransfer
    {
        $orderTransfer->setTotalOrderCount(0);
        if ($orderTransfer->getCustomerReference()) {
            $totalCustomerOrderCount = $this->salesRepository->getTotalCustomerOrderCount($orderTransfer);
            $orderTransfer->setTotalOrderCount($totalCustomerOrderCount);
        }

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function expandOrderTransferWithUniqueProductsQuantity(OrderTransfer $orderTransfer): OrderTransfer
    {
        $uniqueProductQuantity = $this->salesRepository->countUniqueProductsForOrder($orderTransfer->getIdSalesOrder());
        $orderTransfer->setUniqueProductQuantity($uniqueProductQuantity);

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function executeHydrateOrderPlugins(OrderTransfer $orderTransfer): OrderTransfer
    {
        foreach ($this->hydrateOrderPlugins as $hydrateOrderPlugin) {
            $orderTransfer = $hydrateOrderPlugin->hydrate($orderTransfer);
        }

        return $orderTransfer;
    }
}
