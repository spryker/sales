<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Propel\Runtime\Collection\Collection;

interface SalesOrderItemMapperInterface
{
    public function mapSpySalesOrderItemEntityToSalesOrderItemEntity(
        SpySalesOrderItem $salesOrderItemEntity,
        SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer
    ): SpySalesOrderItemEntityTransfer;

    public function mapSalesOrderItemEntityToSpySalesOrderItemEntity(
        SpySalesOrderItemEntityTransfer $salesOrderItemEntity,
        SpySalesOrderItem $salesOrderItem
    ): SpySalesOrderItem;

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\Sales\Persistence\SpySalesOrderItem> $salesOrderItemEntities
     *
     * @return array<\Generated\Shared\Transfer\ItemTransfer>
     */
    public function mapSalesOrderItemEntityCollectionToOrderItemTransfers(
        Collection $salesOrderItemEntities
    ): array;

    public function mapSalesOrderItemEntityTransferToSalesOrderItemEntity(
        SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer,
        SpySalesOrderItem $salesOrderItemEntity
    ): SpySalesOrderItem;

    public function mapSalesOrderItemEntityToSalesOrderItemEntityTransfer(
        SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer,
        SpySalesOrderItem $salesOrderItemEntity
    ): SpySalesOrderItemEntityTransfer;
}
