<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Triggerer;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Sales\Dependency\Facade\SalesToOmsInterface;

class OmsEventTriggerer implements OmsEventTriggererInterface
{
    /**
     * @uses \Spryker\Zed\Oms\OmsConfig::EVENT_CANCEL
     *
     * @var string
     */
    protected const EVENT_CANCEL = 'cancel';

    /**
     * @var \Spryker\Zed\Sales\Dependency\Facade\SalesToOmsInterface
     */
    protected $omsFacade;

    /**
     * @param \Spryker\Zed\Sales\Dependency\Facade\SalesToOmsInterface $omsFacade
     */
    public function __construct(SalesToOmsInterface $omsFacade)
    {
        $this->omsFacade = $omsFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array|null
     */
    public function triggerOrderItemsCancelEvent(OrderTransfer $orderTransfer): ?array
    {
        $salesOrderItemIds = $this->extractSalesOrderItemIds($orderTransfer);

        return $this->omsFacade->triggerEventForOrderItems(static::EVENT_CANCEL, $salesOrderItemIds);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return list<int>
     */
    protected function extractSalesOrderItemIds(OrderTransfer $orderTransfer): array
    {
        $salesOrderItemIds = [];
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $salesOrderItemIds[] = $itemTransfer->requireIdSalesOrderItem()->getIdSalesOrderItem();
        }

        return $salesOrderItemIds;
    }
}
