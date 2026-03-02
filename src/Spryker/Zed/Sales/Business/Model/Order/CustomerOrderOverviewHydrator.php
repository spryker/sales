<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Model\Order;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;

class CustomerOrderOverviewHydrator implements CustomerOrderOverviewHydratorInterface
{
    public function hydrateOrderTransfer(SpySalesOrder $orderEntity): OrderTransfer
    {
        $orderTransfer = $this->mapBaseOrderTransfer($orderEntity);
        $this->hydrateOrderTotals($orderEntity, $orderTransfer);

        return $orderTransfer;
    }

    protected function mapBaseOrderTransfer(SpySalesOrder $orderEntity): OrderTransfer
    {
        $orderTransfer = new OrderTransfer();
        $orderTransfer->fromArray((array)$orderEntity->toArray(), true);

        return $orderTransfer;
    }

    protected function hydrateOrderTotals(SpySalesOrder $orderEntity, OrderTransfer $orderTransfer): void
    {
        $salesOrderTotalsEntity = $orderEntity->getLastOrderTotals();

        if (!$salesOrderTotalsEntity) {
            return;
        }

        $totalsTransfer = new TotalsTransfer();
        $totalsTransfer->setGrandTotal($salesOrderTotalsEntity->getGrandTotal());

        $orderTransfer->setTotals($totalsTransfer);
    }
}
