<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Persistence;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\SpySalesOrderAddressEntityTransfer;
use Generated\Shared\Transfer\SpySalesOrderEntityTransfer;
use Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer;

/**
 * @method \Spryker\Zed\Sales\Persistence\SalesPersistenceFactory getFactory()
 */
interface SalesEntityManagerInterface
{
    public function createSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer;

    public function updateSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer;

    public function createSalesOrderAddress(AddressTransfer $addressTransfer): AddressTransfer;

    public function updateSalesOrderAddress(AddressTransfer $addressTransfer): AddressTransfer;

    public function saveOrderEntity(SpySalesOrderEntityTransfer $salesOrderEntityTransfer): SpySalesOrderEntityTransfer;

    public function saveSalesOrderTotals(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void;

    public function saveSalesOrderAddressEntity(SpySalesOrderAddressEntityTransfer $salesOrderAddressEntityTransfer): SpySalesOrderAddressEntityTransfer;

    public function saveSalesOrderItems(SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer): SpySalesOrderItemEntityTransfer;

    /**
     * @param list<\Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer> $salesOrderItemEntityTransfers
     *
     * @return list<\Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer>
     */
    public function saveSalesOrderItemsBatch(array $salesOrderItemEntityTransfers): array;

    public function unsetSalesOrderShippingAddress(int $idSalesOrderAddress): void;

    /**
     * @param list<int> $salesExpenseIds
     *
     * @return void
     */
    public function deleteSalesExpensesBySalesExpenseIds(array $salesExpenseIds): void;

    /**
     * @param list<int> $salesOrderItemIds
     *
     * @return void
     */
    public function deleteSalesOrderItemsBySalesOrderItemIds(array $salesOrderItemIds): void;
}
