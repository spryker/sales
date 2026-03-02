<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Dependency\Facade;

use Generated\Shared\Transfer\MoneyTransfer;

class SalesToMoneyBridge implements SalesToMoneyInterface
{
    /**
     * @var \Spryker\Zed\Money\Business\MoneyFacadeInterface
     */
    protected $moneyFacade;

    /**
     * @param \Spryker\Zed\Money\Business\MoneyFacadeInterface $moneyFacade
     */
    public function __construct($moneyFacade)
    {
        $this->moneyFacade = $moneyFacade;
    }

    public function fromInteger(int $amount, ?string $isoCode = null): MoneyTransfer
    {
        return $this->moneyFacade->fromInteger($amount, $isoCode);
    }

    public function formatWithSymbol(MoneyTransfer $moneyTransfer): string
    {
        return $this->moneyFacade->formatWithSymbol($moneyTransfer);
    }

    public function formatWithoutSymbol(MoneyTransfer $moneyTransfer): string
    {
        return $this->moneyFacade->formatWithoutSymbol($moneyTransfer);
    }
}
