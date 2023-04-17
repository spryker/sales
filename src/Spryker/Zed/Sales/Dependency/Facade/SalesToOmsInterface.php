<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Dependency\Facade;

use Orm\Zed\Oms\Persistence\SpyOmsOrderItemState;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcess;
use Orm\Zed\Sales\Persistence\SpySalesOrder;

interface SalesToOmsInterface
{
    /**
     * @return \Orm\Zed\Oms\Persistence\SpyOmsOrderItemState
     */
    public function getInitialStateEntity(): SpyOmsOrderItemState;

    /**
     * @param string $processName
     *
     * @return \Orm\Zed\Oms\Persistence\SpyOmsOrderProcess
     */
    public function getProcessEntity(string $processName): SpyOmsOrderProcess;

    /**
     * @param int $idOrderItem
     *
     * @return array<string>
     */
    public function getManualEvents(int $idOrderItem): array;

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $order
     * @param string $flag
     *
     * @return array<\Orm\Zed\Sales\Persistence\SpySalesOrderItem>
     */
    public function getItemsWithFlag(SpySalesOrder $order, string $flag): array;

    /**
     * @param int $idSalesOrder
     *
     * @return array<array<string>>
     */
    public function getManualEventsByIdSalesOrder(int $idSalesOrder): array;

    /**
     * @param int $idSalesOrder
     *
     * @return array
     */
    public function getDistinctManualEventsByIdSalesOrder(int $idSalesOrder): array;

    /**
     * @param int $idSalesOrder
     *
     * @return array
     */
    public function getGroupedDistinctManualEventsByIdSalesOrder(int $idSalesOrder): array;

    /**
     * @return array
     */
    public function getOrderItemMatrix(): array;

    /**
     * @param int $idOrder
     *
     * @return bool
     */
    public function isOrderFlaggedExcludeFromCustomer(int $idOrder): bool;

    /**
     * @param string $eventId
     * @param array $orderItemIds
     * @param array<string, mixed> $data
     *
     * @return array|null
     */
    public function triggerEventForOrderItems(string $eventId, array $orderItemIds, array $data = []): ?array;
}
