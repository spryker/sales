<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\StateMachineResolver;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Service\Payment\PaymentServiceInterface;
use Spryker\Zed\Sales\SalesConfig;

class OrderStateMachineResolver implements OrderStateMachineResolverInterface
{
    /**
     * @var \Spryker\Zed\Sales\SalesConfig
     */
    protected $salesConfig;

    /**
     * @var \Spryker\Service\Payment\PaymentServiceInterface
     */
    protected $paymentService;

    /**
     * @param \Spryker\Zed\Sales\SalesConfig $salesConfig
     * @param \Spryker\Service\Payment\PaymentServiceInterface $paymentService
     */
    public function __construct(SalesConfig $salesConfig, PaymentServiceInterface $paymentService)
    {
        $this->salesConfig = $salesConfig;
        $this->paymentService = $paymentService;
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

        $paymentSelectionKey = $this->paymentService->getPaymentSelectionKey($quoteTransfer->getPayment());
        $paymentMethodStatemachine = $this->salesConfig->getPaymentMethodStatemachineMapping()[$paymentSelectionKey];

        if ($paymentMethodStatemachine) {
            return $paymentMethodStatemachine;
        }

        return $this->salesConfig->determineProcessForOrderItem($quoteTransfer, $itemTransfer);
    }
}
