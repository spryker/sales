<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\StateMachineResolver;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface OrderStateMachineResolverInterface
{
    public function resolve(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer): string;
}
