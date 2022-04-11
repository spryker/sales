<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\StateMachineResolver;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Sales\SalesConfig;

class OrderStateMachineResolver implements OrderStateMachineResolverInterface
{
    /**
     * @var \Spryker\Zed\Sales\SalesConfig
     */
    protected $salesConfig;

    /**
     * @param \Spryker\Zed\Sales\SalesConfig $salesConfig
     */
    public function __construct(SalesConfig $salesConfig)
    {
        $this->salesConfig = $salesConfig;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return string
     */
    public function resolve(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer): string
    {
        if ($this->salesConfig->isDefaultProcessForOrderItemDeterminationAllowed() === false) {
            return $this->salesConfig->determineProcessForOrderItem($quoteTransfer, $itemTransfer);
        }

        $paymentSelectionKey = $this->getPaymentSelectionKey($quoteTransfer->getPayment());
        $paymentMethodStatemachine = $this->salesConfig->getPaymentMethodStatemachineMapping()[$paymentSelectionKey] ?? null;

        if ($paymentMethodStatemachine) {
            return $paymentMethodStatemachine;
        }

        return $this->salesConfig->determineProcessForOrderItem($quoteTransfer, $itemTransfer);
    }

    /**
     * Uses `Payment.paymentSelection`.
     * Returns only the first matching string for the pattern `[a-zA-Z0-9_]+`.
     * Returns the unchanged value if there is no match.
     *
     * @example 'foreignPayments[paymentKey]' becomes 'foreignPayments'
     *
     * @param \Generated\Shared\Transfer\PaymentTransfer $paymentTransfer
     *
     * @return string
     */
    protected function getPaymentSelectionKey(PaymentTransfer $paymentTransfer): string
    {
        preg_match('/^([\w]+)/', $paymentTransfer->getPaymentSelectionOrFail(), $matches);

        if (!isset($matches[0])) {
            return $paymentTransfer->getPaymentSelectionOrFail();
        }

        return $matches[0];
    }
}
