<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Communication\Table;

use Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCustomerInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToMoneyInterface;
use Spryker\Zed\Sales\Dependency\Service\SalesToUtilSanitizeInterface;
use Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface;
use Spryker\Zed\Sales\SalesConfig;

class CustomerOrdersTable extends OrdersTable
{
    /**
     * @var string
     */
    protected const BASE_URL = '/sales/customer/';

    /**
     * @uses \Spryker\Zed\Sales\Communication\Controller\CustomerController
     *
     * @var string
     */
    protected const CUSTOMER_ORDERS_TABLE_BASE_URL = '/sales/customer';

    /**
     * @uses \Spryker\Zed\Sales\Communication\Controller\CustomerController::ordersTableAction()
     *
     * @var string
     */
    protected const CUSTOMER_ORDERS_TABLE_URL = '/orders-table?%s=%s';

    /**
     * @var string
     */
    protected $customerReference;

    /**
     * @var \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface
     */
    protected $salesQueryContainer;

    /**
     * @param \Spryker\Zed\Sales\Communication\Table\OrdersTableQueryBuilderInterface $queryBuilder
     * @param \Spryker\Zed\Sales\Dependency\Facade\SalesToMoneyInterface $moneyFacade
     * @param \Spryker\Zed\Sales\Dependency\Service\SalesToUtilSanitizeInterface $sanitizeService
     * @param \Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface $utilDateTimeService
     * @param \Spryker\Zed\Sales\Dependency\Facade\SalesToCustomerInterface $customerFacade
     * @param string $customerReference
     * @param \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface $salesQueryContainer
     * @param array<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesTablePluginInterface> $salesTablePlugins
     */
    public function __construct(
        OrdersTableQueryBuilderInterface $queryBuilder,
        SalesToMoneyInterface $moneyFacade,
        SalesToUtilSanitizeInterface $sanitizeService,
        UtilDateTimeServiceInterface $utilDateTimeService,
        SalesToCustomerInterface $customerFacade,
        $customerReference,
        SalesQueryContainerInterface $salesQueryContainer,
        array $salesTablePlugins = []
    ) {
        parent::__construct(
            $queryBuilder,
            $moneyFacade,
            $sanitizeService,
            $utilDateTimeService,
            $customerFacade,
            $salesTablePlugins,
        );
        $this->customerReference = $customerReference;
        $this->salesQueryContainer = $salesQueryContainer;
        $this->baseUrl = static::BASE_URL;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function configure(TableConfiguration $config)
    {
        $this->baseUrl = static::CUSTOMER_ORDERS_TABLE_BASE_URL;
        $config->setUrl(sprintf(static::CUSTOMER_ORDERS_TABLE_URL, SalesConfig::PARAM_CUSTOMER_REFERENCE, $this->customerReference));

        return parent::configure($config);
    }

    /**
     * @return array
     */
    protected function getHeaderFields()
    {
        return [
            SpySalesOrderTableMap::COL_ID_SALES_ORDER => '#',
            SpySalesOrderTableMap::COL_ORDER_REFERENCE => 'Order Reference',
            SpySalesOrderTableMap::COL_CREATED_AT => 'Created',
            static::ITEM_STATE_NAMES_CSV => 'Order State',
            static::GRAND_TOTAL => 'Grand Total',
            static::NUMBER_OF_ORDER_ITEMS => 'Number of Items',
            static::URL => 'Actions',
        ];
    }

    /**
     * @return array
     */
    protected function getSortableFields()
    {
        return [
            SpySalesOrderTableMap::COL_ID_SALES_ORDER,
            SpySalesOrderTableMap::COL_ORDER_REFERENCE,
            SpySalesOrderTableMap::COL_CREATED_AT,
        ];
    }

    /**
     * @return array
     */
    protected function getSearchableFields()
    {
        return [
            SpySalesOrderTableMap::COL_ID_SALES_ORDER,
            SpySalesOrderTableMap::COL_ORDER_REFERENCE,
            SpySalesOrderTableMap::COL_CREATED_AT,
        ];
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return void
     */
    protected function persistFilters(TableConfiguration $config)
    {
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderQuery
     */
    protected function buildQuery()
    {
        return $this->salesQueryContainer->querySalesOrder()
            ->addLastOrderGrandTotalToResult(static::GRAND_TOTAL)
            ->addItemStateNameAggregationToResult(static::ITEM_STATE_NAMES_CSV)
            ->addItemCountToResult(static::NUMBER_OF_ORDER_ITEMS)
            ->filterByCustomerReference($this->customerReference);
    }

    /**
     * @param array $item
     *
     * @return string
     */
    protected function getGrandTotal(array $item)
    {
        $currencyIsoCode = $item[SpySalesOrderTableMap::COL_CURRENCY_ISO_CODE];
        if (!isset($item[static::GRAND_TOTAL])) {
            return $this->formatPrice(0, true, $currencyIsoCode);
        }

        return $this->formatPrice((int)$item[static::GRAND_TOTAL], true, $currencyIsoCode);
    }

    /**
     * @param array $queryResults
     *
     * @return array
     */
    protected function formatQueryData(array $queryResults)
    {
        $results = [];
        foreach ($queryResults as $item) {
            $results[] = [
                SpySalesOrderTableMap::COL_ID_SALES_ORDER => $this->formatInt($item[SpySalesOrderTableMap::COL_ID_SALES_ORDER]),
                SpySalesOrderTableMap::COL_ORDER_REFERENCE => $item[SpySalesOrderTableMap::COL_ORDER_REFERENCE],
                SpySalesOrderTableMap::COL_CREATED_AT => $this->utilDateTimeService->formatDateTime($item[SpySalesOrderTableMap::COL_CREATED_AT]),
                static::ITEM_STATE_NAMES_CSV => $this->groupItemStateNames($item[OrdersTableQueryBuilder::FIELD_ITEM_STATE_NAMES_CSV]),
                static::GRAND_TOTAL => $this->getGrandTotal($item),
                static::NUMBER_OF_ORDER_ITEMS => $this->formatInt((int)$item[OrdersTableQueryBuilder::FIELD_NUMBER_OF_ORDER_ITEMS]),
                static::URL => implode(' ', $this->createActionUrls($item)),
            ];
        }

        return $results;
    }
}
