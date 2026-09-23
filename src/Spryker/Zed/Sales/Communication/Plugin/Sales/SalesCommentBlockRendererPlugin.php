<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Communication\Plugin\Sales;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Sales\SalesConfig;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SalesDetailBlockRendererPluginInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\Sales\Communication\SalesCommunicationFactory getFactory()
 * @method \Spryker\Zed\Sales\Business\SalesFacadeInterface getFacade()
 * @method \Spryker\Zed\Sales\SalesConfig getConfig()
 * @method \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface getQueryContainer()
 */
class SalesCommentBlockRendererPlugin extends AbstractPlugin implements SalesDetailBlockRendererPluginInterface
{
    protected const string BLOCK_URL = '/sales/comment/add';

    /**
     * {@inheritDoc}
     * - Checks if the block URL is '/sales/comment/add'.
     *
     * @api
     */
    public function isApplicable(string $blockUrl): bool
    {
        return $blockUrl === static::BLOCK_URL;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $blockUrl
     *
     * @return string
     */
    public function getTemplatePath(string $blockUrl): string
    {
        return '@Sales/Comment/add.twig';
    }

    /**
     * {@inheritDoc}
     * - Creates comment form and returns it as template data.
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
        $formDataProvider = $this->getFactory()->createCommentFormDataProvider();
        $form = $this->getFactory()->getCommentForm(
            $formDataProvider->getData($orderTransfer->getIdSalesOrder()),
            ['action' => $this->buildFormAction($orderTransfer)],
        );

        return ['form' => $form->createView()];
    }

    protected function buildFormAction(OrderTransfer $orderTransfer): string
    {
        $idSalesOrder = $orderTransfer->getIdSalesOrder();

        if ($idSalesOrder === null) {
            return (string)Url::generate(static::BLOCK_URL);
        }

        return (string)Url::generate(static::BLOCK_URL, [
            SalesConfig::PARAM_ID_SALES_ORDER => $idSalesOrder,
        ]);
    }
}
