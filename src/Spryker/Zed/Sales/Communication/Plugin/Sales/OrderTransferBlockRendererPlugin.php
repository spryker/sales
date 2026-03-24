<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Communication\Plugin\Sales;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SalesDetailBlockRendererPluginInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\Sales\Communication\SalesCommunicationFactory getFactory()
 * @method \Spryker\Zed\Sales\Business\SalesFacadeInterface getFacade()
 * @method \Spryker\Zed\Sales\SalesConfig getConfig()
 * @method \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface getQueryContainer()
 */
class OrderTransferBlockRendererPlugin extends AbstractPlugin implements SalesDetailBlockRendererPluginInterface
{
    /**
     * {@inheritDoc}
     * - Checks if the block URL exists in the configuration.
     *
     * @api
     */
    public function isApplicable(string $blockUrl): bool
    {
        return isset($this->getFactory()->getConfig()->getOrderDetailBlockUrlToTemplateMap()[$blockUrl]);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getTemplatePath(string $blockUrl): string
    {
        return $this->getFactory()->getConfig()->getOrderDetailBlockUrlToTemplateMap()[$blockUrl];
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param string $blockUrl
     *
     * @return array<string, mixed>
     */
    public function getData(Request $request, OrderTransfer $orderTransfer, string $blockUrl): array
    {
        return ['order' => $orderTransfer];
    }
}
