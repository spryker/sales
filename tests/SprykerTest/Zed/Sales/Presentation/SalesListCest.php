<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Presentation;

use SprykerTest\Zed\Sales\PageObject\SalesDetailPage;
use SprykerTest\Zed\Sales\PageObject\SalesListPage;
use SprykerTest\Zed\Sales\SalesPresentationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Sales
 * @group Presentation
 * @group SalesListCest
 * Add your own group annotations below this line
 */
class SalesListCest
{
    public function _before(SalesPresentationTester $i): void
    {
        $i->markTestSkipped('Requires refactoring');
        $i->amZed();
        $i->amLoggedInUser();
    }

    public function testOrderListShouldContainOrders(SalesPresentationTester $i, SalesListPage $salesListPage): void
    {
        $i->markTestSkipped('Requires refactoring');
        $i->createOrderWithOneItem();
        $salesListPage->seeListOfOrders();
    }

    public function testICanGoToLatestOrderDetailsPage(SalesPresentationTester $i, SalesListPage $salesListPage): void
    {
        $i->markTestSkipped('Requires refactoring');
        $i->createOrderWithOneItem();

        $latestOrderId = $salesListPage->grabLatestOrderId();
        $url = SalesDetailPage::getOrderDetailsPageUrl($latestOrderId);

        $i->amOnPage($url);
        $i->canSeeCurrentUrlEquals($url);
    }

    public function breadcrumbIsVisible(SalesPresentationTester $i, SalesListPage $salesListPage): void
    {
        $i->markTestSkipped('Requires refactoring');
        $i->createOrderWithOneItem();

        $latestOrderId = $salesListPage->grabLatestOrderId();
        $url = SalesDetailPage::getOrderDetailsPageUrl($latestOrderId);

        $i->amOnPage($url);
        $i->seeBreadcrumbNavigation('Sales / Orders / Order Overview');
    }
}
