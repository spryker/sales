<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Persistence;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\OrderCollectionTransfer;
use Generated\Shared\Transfer\OrderCriteriaTransfer;
use Generated\Shared\Transfer\OrderFilterTransfer;
use Generated\Shared\Transfer\OrderItemFilterTransfer;
use Generated\Shared\Transfer\OrderListRequestTransfer;
use Generated\Shared\Transfer\OrderListTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\SalesExpenseCollectionDeleteCriteriaTransfer;

interface SalesRepositoryInterface
{
    public function findCustomerOrderIdByOrderReference(string $customerReference, string $orderReference): ?int;

    public function findOrderAddressByIdOrderAddress(int $idOrderAddress): ?AddressTransfer;

    public function getCustomerOrderListByCustomerReference(OrderListRequestTransfer $orderListRequestTransfer): OrderListTransfer;

    /**
     * @param \Generated\Shared\Transfer\OrderItemFilterTransfer $orderItemFilterTransfer
     *
     * @return array<\Generated\Shared\Transfer\ItemTransfer>
     */
    public function getOrderItems(OrderItemFilterTransfer $orderItemFilterTransfer): array;

    public function searchOrders(OrderListTransfer $orderListTransfer): OrderListTransfer;

    /**
     * @param array<int> $salesOrderIds
     *
     * @return array<\Generated\Shared\Transfer\ItemTransfer>
     */
    public function getSalesOrderItemsByOrderIds(array $salesOrderIds): array;

    /**
     * @param array<int> $salesOrderIds
     *
     * @return array<\Generated\Shared\Transfer\TotalsTransfer>
     */
    public function getMappedSalesOrderTotalsBySalesOrderIds(array $salesOrderIds): array;

    /**
     * @param array<int> $salesOrderIds
     *
     * @return array<string>
     */
    public function getCurrencyIsoCodesBySalesOrderIds(array $salesOrderIds): array;

    public function getSalesOrderDetails(OrderFilterTransfer $orderFilterTransfer): OrderTransfer;

    public function getTotalCustomerOrderCount(OrderTransfer $orderTransfer): int;

    public function countUniqueProductsForOrder(int $idSalesOrder): int;

    public function getOrderCollection(OrderCriteriaTransfer $orderCriteriaTransfer): OrderCollectionTransfer;

    public function findOrderWithoutItems(OrderFilterTransfer $orderFilterTransfer): ?OrderTransfer;

    /**
     * @param \Generated\Shared\Transfer\SalesExpenseCollectionDeleteCriteriaTransfer $salesExpenseCollectionDeleteCriteriaTransfer
     *
     * @return list<\Generated\Shared\Transfer\ExpenseTransfer>
     */
    public function getSalesExpensesBySalesExpenseCollectionDeleteCriteria(
        SalesExpenseCollectionDeleteCriteriaTransfer $salesExpenseCollectionDeleteCriteriaTransfer
    ): array;

    /**
     * @return list<string>
     */
    public function getOmsOrderItemStates(): array;
}
