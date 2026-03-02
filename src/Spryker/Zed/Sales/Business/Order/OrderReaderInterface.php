<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace Spryker\Zed\Sales\Business\Order;

use Generated\Shared\Transfer\OrderCriteriaTransfer;
use Generated\Shared\Transfer\OrderTransfer;

interface OrderReaderInterface
{
    /**
     * @param int $idSalesOrder
     *
     * @return array<string>
     */
    public function getDistinctOrderStates(int $idSalesOrder): array;

    public function findOrderByOrderCriteria(OrderCriteriaTransfer $orderCriteriaTransfer): ?OrderTransfer;

    public function findOrderByIdSalesOrder(int $idSalesOrder): ?OrderTransfer;

    public function findOrderByIdSalesOrderItem(int $idSalesOrderItem): ?OrderTransfer;
}
