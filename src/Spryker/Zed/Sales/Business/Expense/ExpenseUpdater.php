<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Expense;

use Generated\Shared\Transfer\ExpenseTransfer;
use Spryker\Zed\Sales\Persistence\SalesEntityManagerInterface;

class ExpenseUpdater implements ExpenseUpdaterInterface
{
    /**
     * @var \Spryker\Zed\Sales\Persistence\SalesEntityManagerInterface
     */
    protected $salesEntityManager;

    public function __construct(SalesEntityManagerInterface $salesEntityManager)
    {
        $this->salesEntityManager = $salesEntityManager;
    }

    public function updateSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer
    {
        return $this->salesEntityManager->updateSalesExpense($expenseTransfer);
    }
}
