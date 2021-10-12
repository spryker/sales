<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Communication\TableExpander;

use ArrayObject;

class OrderItemsTableExpander implements OrderItemsTableExpanderInterface
{
    /**
     * @var array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderItemsTableExpanderPluginInterface>
     */
    protected $orderItemsTableExpanderPlugins;

    /**
     * @param array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderItemsTableExpanderPluginInterface> $orderItemsTableExpanderPlugins
     */
    public function __construct(array $orderItemsTableExpanderPlugins)
    {
        $this->orderItemsTableExpanderPlugins = $orderItemsTableExpanderPlugins;
    }

    /**
     * @return array<string>
     */
    public function getColumnHeaders(): array
    {
        $columnHeaders = [];
        foreach ($this->orderItemsTableExpanderPlugins as $orderItemsTableExpanderPlugin) {
            $columnHeaders[] = $orderItemsTableExpanderPlugin->getColumnName();
        }

        return $columnHeaders;
    }

    /**
     * @phpstan-return array<int, string[]>
     *
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     *
     * @return array<string>
     */
    public function getColumnCellsContent(ArrayObject $itemTransfers): array
    {
        $columnCellsContentGroupedByIdItem = [];
        foreach ($itemTransfers as $itemTransfer) {
            foreach ($this->orderItemsTableExpanderPlugins as $orderItemsTableExpanderPlugin) {
                $columnCellsContentGroupedByIdItem[$itemTransfer->getIdSalesOrderItem()][] = $orderItemsTableExpanderPlugin->getColumnCellContent($itemTransfer);
            }
        }

        return $columnCellsContentGroupedByIdItem;
    }
}
