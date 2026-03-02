<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Expander;

use Generated\Shared\Transfer\AddressTransfer;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCustomerInterface;
use Spryker\Zed\Sales\Persistence\SalesRepositoryInterface;

class SalesAddressExpander implements SalesAddressExpanderInterface
{
    /**
     * @var \Spryker\Zed\Sales\Dependency\Facade\SalesToCustomerInterface
     */
    protected $customerFacade;

    /**
     * @var \Spryker\Zed\Sales\Persistence\SalesRepositoryInterface
     */
    protected $repository;

    public function __construct(SalesToCustomerInterface $customerFacade, SalesRepositoryInterface $repository)
    {
        $this->customerFacade = $customerFacade;
        $this->repository = $repository;
    }

    public function expandWithCustomerOrSalesAddress(AddressTransfer $addressTransfer): AddressTransfer
    {
        if ($addressTransfer->getIdCustomerAddress() === null) {
            return $this->expandWithSalesAddress($addressTransfer);
        }

        return $this->expandWithCustomerAddress($addressTransfer);
    }

    protected function expandWithCustomerAddress(AddressTransfer $addressTransfer): AddressTransfer
    {
        $idCustomerAddress = $addressTransfer->getIdCustomerAddress();
        if ($idCustomerAddress === null) {
            return $addressTransfer;
        }

        $foundAddressTransfer = $this->customerFacade->findCustomerAddressById($idCustomerAddress);
        if ($foundAddressTransfer === null) {
            return $addressTransfer;
        }

        return $foundAddressTransfer->setIdSalesOrderAddress($addressTransfer->getIdSalesOrderAddress());
    }

    protected function expandWithSalesAddress(AddressTransfer $addressTransfer): AddressTransfer
    {
        $idSalesOrderAddress = $addressTransfer->getIdSalesOrderAddress();
        if ($idSalesOrderAddress === null) {
            return $addressTransfer;
        }

        $foundAddressTransfer = $this->repository->findOrderAddressByIdOrderAddress($addressTransfer->getIdSalesOrderAddress());
        if ($foundAddressTransfer === null) {
            return $addressTransfer;
        }

        return $foundAddressTransfer->fromArray($addressTransfer->modifiedToArray(), true);
    }
}
