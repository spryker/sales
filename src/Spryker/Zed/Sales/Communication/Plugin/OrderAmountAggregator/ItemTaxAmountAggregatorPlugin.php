<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Sales\Communication\Plugin\OrderAmountAggregator;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Sales\Dependency\Plugin\OrderTotalsAggregatePluginInterface;

/**
 * @method \Spryker\Zed\Sales\Business\SalesFacade getFacade()
 */
class ItemTaxAmountAggregatorPlugin extends AbstractPlugin implements OrderTotalsAggregatePluginInterface
{

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function aggregate(OrderTransfer $orderTransfer)
    {
        $this->getFacade()->aggregateOrderItemTaxAmount($orderTransfer);
    }

}