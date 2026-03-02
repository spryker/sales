<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Sales\Helper;

use Codeception\Module;

trait SalesHelperTrait
{
    protected function getSalesHelper(): SalesHelper
    {
        /** @var \SprykerTest\Shared\Sales\Helper\SalesHelper $salesHelper */
        $salesHelper = $this->getModule('\\' . SalesHelper::class);

        return $salesHelper;
    }

    abstract protected function getModule(string $name): Module;
}
