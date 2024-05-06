<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Model\Customer;

use ArrayObject;
use Generated\Shared\Transfer\OrderListTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Propel\Runtime\Collection\Collection;
use Spryker\Zed\Sales\Business\StrategyResolver\OrderHydratorStrategyResolverInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToOmsInterface;
use Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface;

class CustomerOrderReader implements CustomerOrderReaderInterface
{
    /**
     * @var \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @var \Spryker\Zed\Sales\Business\StrategyResolver\OrderHydratorStrategyResolverInterface
     */
    protected $orderHydratorStrategyResolver;

    /**
     * @var \Spryker\Zed\Sales\Dependency\Facade\SalesToOmsInterface
     */
    protected $omsFacade;

    /**
     * @var array<\Spryker\Zed\SalesExtension\Dependency\Plugin\SearchOrderExpanderPluginInterface>
     */
    protected $searchOrderExpanderPlugins;

    /**
     * @param \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface $queryContainer
     * @param \Spryker\Zed\Sales\Business\StrategyResolver\OrderHydratorStrategyResolverInterface $orderHydratorStrategyResolver
     * @param array<\Spryker\Zed\SalesExtension\Dependency\Plugin\SearchOrderExpanderPluginInterface> $searchOrderExpanderPlugins
     * @param \Spryker\Zed\Sales\Dependency\Facade\SalesToOmsInterface|null $omsFacade
     */
    public function __construct(
        SalesQueryContainerInterface $queryContainer,
        OrderHydratorStrategyResolverInterface $orderHydratorStrategyResolver,
        array $searchOrderExpanderPlugins,
        ?SalesToOmsInterface $omsFacade = null
    ) {
        $this->queryContainer = $queryContainer;
        $this->orderHydratorStrategyResolver = $orderHydratorStrategyResolver;
        $this->searchOrderExpanderPlugins = $searchOrderExpanderPlugins;
        $this->omsFacade = $omsFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderListTransfer $orderListTransfer
     * @param int $idCustomer
     *
     * @return \Generated\Shared\Transfer\OrderListTransfer
     */
    public function getOrders(OrderListTransfer $orderListTransfer, $idCustomer)
    {
        $orderCollection = $this
            ->queryContainer
            ->queryCustomerOrders($idCustomer, $orderListTransfer->getFilter())
            ->find();

        $orders = $this->hydrateOrderListCollectionTransferFromEntityCollection($orderCollection);
        $orderTransfers = $this->executeSearchOrderExpanderPlugins($orders->getArrayCopy());
        $orderListTransfer->setOrders(new ArrayObject($orderTransfers));

        return $orderListTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\Sales\Persistence\SpySalesOrder> $orderCollection
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\OrderTransfer>
     */
    protected function hydrateOrderListCollectionTransferFromEntityCollection(Collection $orderCollection)
    {
        $orders = new ArrayObject();
        foreach ($orderCollection as $salesOrderEntity) {
            if ($salesOrderEntity->countItems() === 0) {
                continue;
            }

            if ($this->excludeOrder($salesOrderEntity)) {
                continue;
            }

            $orderTransfer = $this->orderHydratorStrategyResolver
                ->resolveByOrderItemEntities($salesOrderEntity->getItems())
                ->hydrateOrderTransferFromPersistenceByIdSalesOrder(
                    $salesOrderEntity->getIdSalesOrder(),
                );

            $orders->append($orderTransfer);
        }

        return $orders;
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     *
     * @return bool
     */
    protected function excludeOrder(SpySalesOrder $salesOrderEntity)
    {
        if (!$this->hasOmsFacade()) {
            return false;
        }

        $excludeFromCustomer = $this->omsFacade->isOrderFlaggedExcludeFromCustomer(
            $salesOrderEntity->getIdSalesOrder(),
        );

        return $excludeFromCustomer;
    }

    /**
     * @deprecated Will be removed in next major. Make OMS facade dependency required.
     *
     * @return bool
     */
    protected function hasOmsFacade()
    {
        return $this->omsFacade !== null;
    }

    /**
     * @param array<\Generated\Shared\Transfer\OrderTransfer> $orderTransfers
     *
     * @return array<\Generated\Shared\Transfer\OrderTransfer>
     */
    protected function executeSearchOrderExpanderPlugins(array $orderTransfers): array
    {
        foreach ($this->searchOrderExpanderPlugins as $searchOrderExpanderPlugin) {
            $orderTransfers = $searchOrderExpanderPlugin->expand($orderTransfers);
        }

        return $orderTransfers;
    }
}
