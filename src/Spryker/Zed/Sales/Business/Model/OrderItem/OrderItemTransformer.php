<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Business\Model\OrderItem;

use ArrayObject;
use Generated\Shared\Transfer\ItemCollectionTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ProductOptionTransfer;

class OrderItemTransformer implements OrderItemTransformerInterface
{
    public function transformSplittableItem(ItemTransfer $itemTransfer): ItemCollectionTransfer
    {
        $transformedItemsCollection = new ItemCollectionTransfer();

        $quantity = $itemTransfer->getQuantity();

        if ((int)$quantity === 1) {
            return $transformedItemsCollection->addItem($itemTransfer);
        }

        $transformedItemTransfer = new ItemTransfer();
        $transformedItemTransfer->fromArray($itemTransfer->toArray(), true);
        $transformedItemTransfer->setQuantity(1);

        for ($i = 1; $quantity >= $i; $i++) {
            $preparedTransformedItemTransfer = clone $transformedItemTransfer;
            $transformedProductOptions = new ArrayObject();
            foreach ($itemTransfer->getProductOptions() as $productOptionTransfer) {
                $transformedProductOptions->append($this->copyProductOptionTransfer($productOptionTransfer));
            }

            $preparedTransformedItemTransfer->setProductOptions($transformedProductOptions);
            $transformedItemsCollection->addItem($preparedTransformedItemTransfer);
        }

        return $transformedItemsCollection;
    }

    protected function copyProductOptionTransfer(ProductOptionTransfer $productOptionTransfer): ProductOptionTransfer
    {
        $transformedProductOptionTransfer = new ProductOptionTransfer();
        $transformedProductOptionTransfer->fromArray($productOptionTransfer->toArray(), true);

        $transformedProductOptionTransfer
            ->setQuantity(1)
            ->setIdProductOptionValue(null);

        return $transformedProductOptionTransfer;
    }
}
