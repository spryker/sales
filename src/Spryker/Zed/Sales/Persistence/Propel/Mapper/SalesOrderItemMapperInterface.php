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
    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $salesOrderItemEntity
     * @param \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer
     *
     * @return \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer
     */
    public function mapSpySalesOrderItemEntityToSalesOrderItemEntity(
        SpySalesOrderItem $salesOrderItemEntity,
        SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer
    ): SpySalesOrderItemEntityTransfer;

    /**
     * @param \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer $salesOrderItemEntity
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $salesOrderItem
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItem
     */
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

    /**
     * @param \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $salesOrderItemEntity
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItem
     */
    public function mapSalesOrderItemEntityTransferToSalesOrderItemEntity(
        SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer,
        SpySalesOrderItem $salesOrderItemEntity
    ): SpySalesOrderItem;

    /**
     * @param \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $salesOrderItemEntity
     *
     * @return \Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer
     */
    public function mapSalesOrderItemEntityToSalesOrderItemEntityTransfer(
        SpySalesOrderItemEntityTransfer $salesOrderItemEntityTransfer,
        SpySalesOrderItem $salesOrderItemEntity
    ): SpySalesOrderItemEntityTransfer;
}
