<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Model\OrderItem;

use Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;

/**
 * @deprecated Use {@link \Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderItemMapperInterface} instead.
 */
interface SalesOrderItemMapperInterface
{
    public function mapSpySalesOrderItemEntityToSalesOrderItemEntity(SpySalesOrderItem $spySalesOrderItemEntity): SpySalesOrderItemEntityTransfer;

    public function mapSalesOrderItemEntityToSpySalesOrderItemEntity(SpySalesOrderItemEntityTransfer $salesOrderItemEntity): SpySalesOrderItem;
}
