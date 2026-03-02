<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Address;

use Generated\Shared\Transfer\AddressTransfer;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCountryInterface;
use Spryker\Zed\Sales\Persistence\SalesEntityManagerInterface;

class OrderAddressWriter implements OrderAddressWriterInterface
{
    /**
     * @var \Spryker\Zed\Sales\Persistence\SalesEntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Spryker\Zed\Sales\Dependency\Facade\SalesToCountryInterface
     */
    protected $countryFacade;

    public function __construct(
        SalesEntityManagerInterface $entityManager,
        SalesToCountryInterface $countryFacade
    ) {
        $this->entityManager = $entityManager;
        $this->countryFacade = $countryFacade;
    }

    public function create(AddressTransfer $addressTransfer): AddressTransfer
    {
        $addressTransfer->setFkCountry(
            $this->countryFacade->getCountryByIso2Code($addressTransfer->getIso2Code())->getIdCountryOrFail(),
        );

        if ($addressTransfer->getIdSalesOrderAddress()) {
            $this->update($addressTransfer, $addressTransfer->getIdSalesOrderAddress());

            return $addressTransfer;
        }

        return $this->entityManager->createSalesOrderAddress($this->cleanUpAddressUuid($addressTransfer));
    }

    public function update(AddressTransfer $addressTransfer, int $idAddress): bool
    {
        if ($idAddress !== 0) {
            $addressTransfer->setIdSalesOrderAddress($idAddress);
        }

        if ($addressTransfer->getIdCustomerAddress() === null && $addressTransfer->getIdSalesOrderAddress() === null) {
            return false;
        }

        $this->entityManager->updateSalesOrderAddress($this->cleanUpAddressUuid($addressTransfer));

        return true;
    }

    protected function cleanUpAddressUuid(AddressTransfer $addressTransfer): AddressTransfer
    {
        return $addressTransfer->setUuid(null);
    }
}
