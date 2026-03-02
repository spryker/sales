<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Mapper;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SpySalesOrderEntityTransfer;

class SalesOrderMapper implements SalesOrderMapperInterface
{
    public function mapQuoteTransferToOrderTransfer(QuoteTransfer $quoteTransfer, OrderTransfer $orderTransfer): OrderTransfer
    {
        $orderTransfer = $this->mapCustomerTransferToSalesOrderTransfer($quoteTransfer->getCustomerOrFail(), $orderTransfer);
        $orderTransfer->setCurrencyIsoCode($quoteTransfer->getCurrencyOrFail()->getCodeOrFail());
        $orderTransfer->setPriceMode($quoteTransfer->getPriceModeOrFail());
        $orderTransfer->setStore($quoteTransfer->getStoreOrFail()->getNameOrFail());

        return $orderTransfer;
    }

    public function mapOrderTransferToSalesOrderEntityTransfer(
        OrderTransfer $orderTransfer,
        SpySalesOrderEntityTransfer $salesOrderEntityTransfer
    ): SpySalesOrderEntityTransfer {
        $salesOrderEntityTransfer->fromArray($orderTransfer->toArray(), true);
        if ($orderTransfer->getBillingAddress()) {
            $salesOrderEntityTransfer->setFkSalesOrderAddressBilling($orderTransfer->getBillingAddressOrFail()->getIdSalesOrderAddressOrFail());
        }

        return $this->mapOrderShippingAddressToSalesOrderEntityTransfer($orderTransfer, $salesOrderEntityTransfer);
    }

    protected function mapCustomerTransferToSalesOrderTransfer(
        CustomerTransfer $customerTransfer,
        OrderTransfer $orderTransfer
    ): OrderTransfer {
        $orderTransfer->setCustomerReference($customerTransfer->getCustomerReferenceOrFail());
        $orderTransfer->setSalutation($customerTransfer->getSalutation());
        $orderTransfer->setEmail($customerTransfer->getEmail());
        $orderTransfer->setFirstName($customerTransfer->getFirstName());
        $orderTransfer->setLastName($customerTransfer->getLastName());

        return $orderTransfer;
    }

    /**
     * @deprecated Exists for backward compatibility reasons only. Will be removed in the next major.
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\SpySalesOrderEntityTransfer $salesOrderEntityTransfer
     *
     * @return \Generated\Shared\Transfer\SpySalesOrderEntityTransfer
     */
    protected function mapOrderShippingAddressToSalesOrderEntityTransfer(
        OrderTransfer $orderTransfer,
        SpySalesOrderEntityTransfer $salesOrderEntityTransfer
    ): SpySalesOrderEntityTransfer {
        if ($orderTransfer->getShippingAddress() && $orderTransfer->getShippingAddressOrFail()->getIdSalesOrderAddress()) {
            $salesOrderEntityTransfer->setFkSalesOrderAddressShipping($orderTransfer->getShippingAddressOrFail()->getIdSalesOrderAddressOrFail());
        }

        if ($salesOrderEntityTransfer->getFkSalesOrderAddressShipping() && $orderTransfer->getShippingAddress() === null && $orderTransfer->isPropertyModified(OrderTransfer::SHIPPING_ADDRESS)) {
            $salesOrderEntityTransfer->setFkSalesOrderAddressShipping(null);
        }

        return $salesOrderEntityTransfer;
    }
}
