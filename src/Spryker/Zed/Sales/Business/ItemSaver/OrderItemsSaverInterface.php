<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\ItemSaver;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;

interface OrderItemsSaverInterface
{
    public function saveOrderItems(
        QuoteTransfer $quoteTransfer,
        SaveOrderTransfer $saveOrderTransfer,
        ?bool $skipOrderItemsPostSavePlugins = false
    ): SaveOrderTransfer;
}
