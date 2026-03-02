<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Model\OrderItem;

use Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;

/**
 * @deprecated Use {@link \Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderItemMapper} instead.
 */
class SalesOrderItemMapper implements SalesOrderItemMapperInterface
{
    public function mapSpySalesOrderItemEntityToSalesOrderItemEntity(SpySalesOrderItem $spySalesOrderItemEntity): SpySalesOrderItemEntityTransfer
    {
        $salesOrderItemEntity = (new SpySalesOrderItemEntityTransfer())
            ->fromArray($spySalesOrderItemEntity->toArray(), true);

        return $salesOrderItemEntity;
    }

    public function mapSalesOrderItemEntityToSpySalesOrderItemEntity(SpySalesOrderItemEntityTransfer $salesOrderItemEntity): SpySalesOrderItem
    {
        $spySalesOrderItemEntity = new SpySalesOrderItem();
        $spySalesOrderItemEntity->fromArray($salesOrderItemEntity->toArray(true));

        return $spySalesOrderItemEntity;
    }
}
