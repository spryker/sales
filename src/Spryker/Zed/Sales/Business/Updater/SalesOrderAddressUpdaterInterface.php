<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Updater;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface SalesOrderAddressUpdaterInterface
{
    public function updateSalesOrderAddressesByQuote(
        QuoteTransfer $quoteTransfer,
        OrderTransfer $orderTransfer
    ): OrderTransfer;
}
