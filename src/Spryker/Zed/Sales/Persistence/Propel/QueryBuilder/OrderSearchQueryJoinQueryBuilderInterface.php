<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Persistence\Propel\QueryBuilder;

use Generated\Shared\Transfer\QueryJoinCollectionTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;

interface OrderSearchQueryJoinQueryBuilderInterface
{
    public function addSalesOrderQueryFilters(
        SpySalesOrderQuery $salesOrderQuery,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): SpySalesOrderQuery;
}
