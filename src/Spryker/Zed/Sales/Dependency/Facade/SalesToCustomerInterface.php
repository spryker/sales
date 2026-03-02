<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Dependency\Facade;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface SalesToCustomerInterface
{
    public function findByReference(string $customerReference): ?CustomerTransfer;

    public function findCustomerAddressById(int $idCustomerAddress): ?AddressTransfer;

    public function findCustomerById(CustomerTransfer $customerTransfer): ?CustomerTransfer;
}
