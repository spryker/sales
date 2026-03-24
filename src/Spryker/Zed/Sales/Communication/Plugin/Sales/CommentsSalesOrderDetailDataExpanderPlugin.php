<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Communication\Plugin\Sales;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SalesOrderDetailDataExpanderPluginInterface;

/**
 * @method \Spryker\Zed\Sales\Communication\SalesCommunicationFactory getFactory()
 * @method \Spryker\Zed\Sales\Business\SalesFacadeInterface getFacade()
 * @method \Spryker\Zed\Sales\SalesConfig getConfig()
 * @method \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface getQueryContainer()
 */
class CommentsSalesOrderDetailDataExpanderPlugin extends AbstractPlugin implements SalesOrderDetailDataExpanderPluginInterface
{
    protected const string KEY_COMMENTS = 'comments';

    /**
     * {@inheritDoc}
     * - Expands order detail data with order comments.
     * - Fetches all comments for the given order.
     * - Adds `comments` array to the order detail data.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param array<string, mixed> $orderDetailData
     *
     * @return array<string, mixed>
     */
    public function expand(OrderTransfer $orderTransfer, array $orderDetailData): array
    {
        $orderDetailData[static::KEY_COMMENTS] = $this->getFacade()->getOrderCommentsByIdSalesOrder($orderTransfer->getIdSalesOrder())->getComments();

        return $orderDetailData;
    }
}
