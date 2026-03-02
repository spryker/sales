<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Expander;

use Generated\Shared\Transfer\AddressTransfer;

interface SalesAddressExpanderInterface
{
    public function expandWithCustomerOrSalesAddress(AddressTransfer $addressTransfer): AddressTransfer;
}
