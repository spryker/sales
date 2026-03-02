<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Dependency\Facade;

use Generated\Shared\Transfer\MoneyTransfer;

interface SalesToMoneyInterface
{
    public function fromInteger(int $amount, ?string $isoCode = null): MoneyTransfer;

    public function formatWithSymbol(MoneyTransfer $moneyTransfer): string;

    public function formatWithoutSymbol(MoneyTransfer $moneyTransfer): string;
}
