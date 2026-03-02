<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Dependency\Service;

interface SalesToUtilSanitizeInterface
{
    public function escapeHtml(string $text, bool $double = true, ?string $charset = null): string;
}
