<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Address;

use Generated\Shared\Transfer\AddressTransfer;

interface OrderAddressWriterInterface
{
    public function update(AddressTransfer $addressTransfer, int $idAddress): bool;

    public function create(AddressTransfer $addressTransfer): AddressTransfer;
}
