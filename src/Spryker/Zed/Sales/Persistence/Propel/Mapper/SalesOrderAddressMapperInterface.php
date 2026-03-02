<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddress;

interface SalesOrderAddressMapperInterface
{
    public function mapAddressTransferToSalesOrderAddressEntity(AddressTransfer $addressTransfer): SpySalesOrderAddress;

    public function mapAddressEntityToAddressTransfer(AddressTransfer $addressTransfer, SpySalesOrderAddress $salesOrderAddressEntity): AddressTransfer;
}
