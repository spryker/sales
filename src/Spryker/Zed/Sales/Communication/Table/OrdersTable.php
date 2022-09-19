<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Communication\Table;

use Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCustomerInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToMoneyInterface;
use Spryker\Zed\Sales\Dependency\Service\SalesToUtilSanitizeInterface;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SalesTablePluginInterface;

class OrdersTable extends AbstractTable
{
    public const URL = SalesTablePluginInterface::ROW_ACTIONS;

    /**
     * @var string
     */
    public const ID_ORDER_ITEM_PROCESS = 'id-order-item-process';

    /**
     * @var string
     */
    public const ID_ORDER_ITEM_STATE = 'id-order-item-state';

    /**
     * @var string
     */
    public const FILTER = 'filter';

    /**
     * @var string
     */
    public const URL_SALES_DETAIL = '/sales/detail';

    /**
     * @var string
     */
    public const PARAM_ID_SALES_ORDER = 'id-sales-order';

    /**
     * @var string
     */
    public const GRAND_TOTAL = 'GrandTotal';

    /**
     * @var string
     */
    public const ITEM_STATE_NAMES_CSV = 'item_state_names_csv';

    /**
     * @var string
     */
    public const NUMBER_OF_ORDER_ITEMS = 'number_of_order_items';

    /**
     * @var string
     */
    protected const COLUMN_SEPARATOR = ' ';

    /**
     * @var string
     */
    protected const FULL_NAME_SEARCHABLE_FIELD_PATTERN = 'CONCAT(%s,\'%s\',%s)';

    /**
     * @var \Spryker\Zed\Sales\Communication\Table\OrdersTableQueryBuilderInterface
     */
    protected $queryBuilder;

    /**
     * @var \Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface
     */
    protected $utilDateTimeService;

    /**
     * @var \Spryker\Zed\Sales\Dependency\Facade\SalesToMoneyInterface
     */
    protected $moneyFacade;

    /**
     * @var \Spryker\Zed\Sales\Dependency\Service\SalesToUtilSanitizeInterface
     */
    protected $sanitizeService;

    /**
     * @var \Spryker\Zed\Sales\Dependency\Facade\SalesToCustomerInterface
     */
    protected $customerFacade;

    /**
     * @var array<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesTablePluginInterface>
     */
    protected $salesTablePlugins;

    /**
     * @param \Spryker\Zed\Sales\Communication\Table\OrdersTableQueryBuilderInterface $queryBuilder
     * @param \Spryker\Zed\Sales\Dependency\Facade\SalesToMoneyInterface $moneyFacade
     * @param \Spryker\Zed\Sales\Dependency\Service\SalesToUtilSanitizeInterface $sanitizeService
     * @param \Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface $utilDateTimeService
     * @param \Spryker\Zed\Sales\Dependency\Facade\SalesToCustomerInterface $customerFacade
     * @param array<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesTablePluginInterface> $salesTablePlugins
     */
    public function __construct(
        OrdersTableQueryBuilderInterface $queryBuilder,
        SalesToMoneyInterface $moneyFacade,
        SalesToUtilSanitizeInterface $sanitizeService,
        UtilDateTimeServiceInterface $utilDateTimeService,
        SalesToCustomerInterface $customerFacade,
        array $salesTablePlugins = []
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->moneyFacade = $moneyFacade;
        $this->sanitizeService = $sanitizeService;
        $this->utilDateTimeService = $utilDateTimeService;
        $this->customerFacade = $customerFacade;
        $this->salesTablePlugins = $salesTablePlugins;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function configure(TableConfiguration $config)
    {
        $config->setHeader($this->getHeaderFields());
        $config->setSearchable($this->getSearchableFields());
        $config->setSortable($this->getSortableFields());

        $config->addRawColumn(static::URL);
        $config->addRawColumn(SpySalesOrderTableMap::COL_CUSTOMER_REFERENCE);
        $config->addRawColumn(SpySalesOrderTableMap::COL_EMAIL);
        $config->addRawColumn(static::ITEM_STATE_NAMES_CSV);

        $config->setDefaultSortColumnIndex(0);
        $config->setDefaultSortDirection(TableConfiguration::SORT_DESC);

        $this->persistFilters($config);

        return $config;
    }

    /**
     * @return array<string>
     */
    protected function getCsvHeaders(): array
    {
        return [
            SpySalesOrderTableMap::COL_ID_SALES_ORDER => '#',
            SpySalesOrderTableMap::COL_ORDER_REFERENCE => 'Order Reference',
            SpySalesOrderTableMap::COL_CREATED_AT => 'Created',
            SpySalesOrderTableMap::COL_CUSTOMER_REFERENCE => 'Customer Full Name',
            SpySalesOrderTableMap::COL_EMAIL => 'Email',
            static::ITEM_STATE_NAMES_CSV => 'Order State',
            static::GRAND_TOTAL => 'Grand Total',
            static::NUMBER_OF_ORDER_ITEMS => 'Number of Items',
        ];
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderQuery
     */
    protected function getDownloadQuery(): ModelCriteria
    {
        $salesOrderQuery = $this->queryBuilder->buildQuery();
        $salesOrderQuery->orderBy(SpySalesOrderTableMap::COL_ID_SALES_ORDER, Criteria::DESC);

        return $salesOrderQuery;
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $entity
     *
     * @return array
     */
    protected function formatCsvRow(ActiveRecordInterface $entity): array
    {
        $salesOrderRow = (array)$entity->toArray(SpySalesOrderTableMap::TYPE_COLNAME);
        $grandTotal = $salesOrderRow[OrdersTableQueryBuilder::FIELD_ORDER_GRAND_TOTAL] ?? 0;
        $customer = sprintf(
            '%s%s %s',
            $entity->getSalutation() ? $entity->getSalutation() . ' ' : '',
            $entity->getFirstName(),
            $entity->getLastName(),
        );

        $stateNames = $salesOrderRow[OrdersTableQueryBuilder::FIELD_ITEM_STATE_NAMES_CSV] ?? '';

        $salesOrderRow[SpySalesOrderTableMap::COL_CUSTOMER_REFERENCE] = $customer;
        $salesOrderRow[SpySalesOrderTableMap::COL_CREATED_AT] = $this->utilDateTimeService->formatDateTime($entity->getCreatedAt());
        $salesOrderRow[static::ITEM_STATE_NAMES_CSV] = $this->groupItemStateNamesForCsv($stateNames);
        $salesOrderRow[static::GRAND_TOTAL] = $this->formatGrandTotal((int)$grandTotal, $entity->getCurrencyIsoCode());
        $salesOrderRow[SpySalesOrderTableMap::COL_ID_SALES_ORDER] = $this->formatInt($salesOrderRow[SpySalesOrderTableMap::COL_ID_SALES_ORDER]);
        $salesOrderRow[static::NUMBER_OF_ORDER_ITEMS] = $this->formatInt((int)$salesOrderRow[static::NUMBER_OF_ORDER_ITEMS]);

        return $salesOrderRow;
    }

    /**
     * @param int $grandTotal
     * @param string $currencyIsoCode
     *
     * @return string
     */
    protected function formatGrandTotal(int $grandTotal, string $currencyIsoCode): string
    {
        return $this->formatPrice($grandTotal, true, $currencyIsoCode);
    }

    /**
     * @param string $itemStateNamesCsv
     *
     * @return string
     */
    protected function groupItemStateNamesForCsv(string $itemStateNamesCsv): string
    {
        $itemStateNames = explode(',', $itemStateNamesCsv);
        $itemStateNames = array_map('trim', $itemStateNames);
        $distinctItemStateNames = array_unique($itemStateNames);

        return implode(' ', $distinctItemStateNames);
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array
     */
    protected function prepareData(TableConfiguration $config)
    {
        $query = $this->buildQuery();
        /** @var array $queryResults */
        $queryResults = $this->runQuery($query, $config);

        return $this->formatQueryData($queryResults);
    }

    /**
     * @param array $item
     *
     * @return string
     */
    protected function getGrandTotal(array $item)
    {
        $currencyIsoCode = $item[SpySalesOrderTableMap::COL_CURRENCY_ISO_CODE];
        if (!isset($item[OrdersTableQueryBuilder::FIELD_ORDER_GRAND_TOTAL])) {
            return $this->formatPrice(0, true, $currencyIsoCode);
        }

        return $this->formatPrice((int)$item[OrdersTableQueryBuilder::FIELD_ORDER_GRAND_TOTAL], true, $currencyIsoCode);
    }

    /**
     * @param array $item
     *
     * @return string
     */
    protected function formatCustomer(array $item)
    {
        $salutation = $item[SpySalesOrderTableMap::COL_SALUTATION];

        $customer = sprintf(
            '%s%s %s',
            $salutation ? $salutation . ' ' : '',
            $item[SpySalesOrderTableMap::COL_FIRST_NAME],
            $item[SpySalesOrderTableMap::COL_LAST_NAME],
        );

        $customer = $this->sanitizeService->escapeHtml($customer);

        if (isset($item[SpySalesOrderTableMap::COL_CUSTOMER_REFERENCE])) {
            $customerTransfer = $this->customerFacade->findByReference(
                $item[SpySalesOrderTableMap::COL_CUSTOMER_REFERENCE],
            );

            if (!$customerTransfer) {
                return $customer;
            }
            $url = Url::generate('/customer/view', [
                'id-customer' => $customerTransfer->getIdCustomer(),
            ]);
            $customer = '<a href="' . $url . '">' . $customer . '</a>';
        }

        return $customer;
    }

    /**
     * @param string $emailAddress
     *
     * @return string
     */
    protected function formatEmailAddress($emailAddress)
    {
        $escapedEmailAddress = $this->sanitizeService->escapeHtml($emailAddress);
        $emailAddressLink = sprintf('<a href="mailto:%1$s">%1$s</a>', $escapedEmailAddress);

        return $emailAddressLink;
    }

    /**
     * @param string $itemStateNamesCsv
     *
     * @return string
     */
    protected function groupItemStateNames($itemStateNamesCsv)
    {
        $itemStateNameLabels = [];

        $itemStateNames = explode(',', $itemStateNamesCsv);
        $itemStateNames = array_map('trim', $itemStateNames);
        $distinctItemStateNames = array_unique($itemStateNames);

        foreach ($distinctItemStateNames as $distinctItemStateName) {
            $itemStateNameLabels[] = $this->generateLabel($distinctItemStateName, null);
        }

        return implode(' ', $itemStateNameLabels);
    }

    /**
     * @param int $value
     * @param bool $includeSymbol
     * @param string|null $currencyIsoCode
     *
     * @return string
     */
    protected function formatPrice($value, $includeSymbol = true, $currencyIsoCode = null)
    {
        $moneyTransfer = $this->moneyFacade->fromInteger($value, $currencyIsoCode);

        if ($includeSymbol) {
            return $this->moneyFacade->formatWithSymbol($moneyTransfer);
        }

        return $this->moneyFacade->formatWithoutSymbol($moneyTransfer);
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function createActionUrls(array $item)
    {
        $urls = [];

        $urls[] = $this->generateViewButton(
            Url::generate(static::URL_SALES_DETAIL, [
                static::PARAM_ID_SALES_ORDER => $item[SpySalesOrderTableMap::COL_ID_SALES_ORDER],
            ]),
            'View',
        );

        return $urls;
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderQuery
     */
    protected function buildQuery()
    {
        $idOrderItemProcess = $this->request->query->getInt(static::ID_ORDER_ITEM_PROCESS);
        $idOrderItemItemState = $this->request->query->getInt(static::ID_ORDER_ITEM_STATE);
        $filter = (string)$this->request->query->get(static::FILTER) ?: null;

        return $this->queryBuilder->buildQuery($idOrderItemProcess, $idOrderItemItemState, $filter);
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return void
     */
    protected function persistFilters(TableConfiguration $config)
    {
        $idOrderItemProcess = $this->request->query->getInt(static::ID_ORDER_ITEM_PROCESS);
        if ($idOrderItemProcess) {
            $idOrderItemState = $this->request->query->getInt(static::ID_ORDER_ITEM_STATE);
            $filter = $this->request->query->get(static::FILTER);

            $config->setUrl(
                sprintf(
                    'table?id-order-item-process=%s&id-order-item-state=%s&filter=%s',
                    $idOrderItemProcess,
                    $idOrderItemState,
                    $filter,
                ),
            );
        }
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
            SpySalesOrderTableMap::COL_CUSTOMER_REFERENCE => 'Customer Full Name',
            SpySalesOrderTableMap::COL_EMAIL => 'Email',
            static::ITEM_STATE_NAMES_CSV => 'Order State',
            static::GRAND_TOTAL => 'Grand Total',
            static::NUMBER_OF_ORDER_ITEMS => 'Number of Items',
            static::URL => 'Actions',
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
            SpySalesOrderTableMap::COL_EMAIL,
            $this->getFullNameSearchableField(),
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
            SpySalesOrderTableMap::COL_EMAIL,
            static::NUMBER_OF_ORDER_ITEMS,
        ];
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
            $itemLine = [
                SpySalesOrderTableMap::COL_ID_SALES_ORDER => $item[SpySalesOrderTableMap::COL_ID_SALES_ORDER],
                SpySalesOrderTableMap::COL_ORDER_REFERENCE => $item[SpySalesOrderTableMap::COL_ORDER_REFERENCE],
                SpySalesOrderTableMap::COL_CREATED_AT => $this->utilDateTimeService->formatDateTime($item[SpySalesOrderTableMap::COL_CREATED_AT]),
                SpySalesOrderTableMap::COL_CUSTOMER_REFERENCE => $this->formatCustomer($item),
                SpySalesOrderTableMap::COL_EMAIL => $this->formatEmailAddress($item[SpySalesOrderTableMap::COL_EMAIL]),
                static::ITEM_STATE_NAMES_CSV => $this->groupItemStateNames($item[OrdersTableQueryBuilder::FIELD_ITEM_STATE_NAMES_CSV]),
                static::GRAND_TOTAL => $this->getGrandTotal($item),
                static::NUMBER_OF_ORDER_ITEMS => $item[OrdersTableQueryBuilder::FIELD_NUMBER_OF_ORDER_ITEMS],
                static::URL => implode(' ', $this->createActionUrls($item)),
            ];
            $itemLine = $this->applyUiPlugins($itemLine);

            $itemLine[SpySalesOrderTableMap::COL_ID_SALES_ORDER] = $this->formatInt($itemLine[SpySalesOrderTableMap::COL_ID_SALES_ORDER]);
            $itemLine[static::NUMBER_OF_ORDER_ITEMS] = $this->formatInt((int)$itemLine[static::NUMBER_OF_ORDER_ITEMS]);

            $results[] = $itemLine;
        }

        return $results;
    }

    /**
     * @param array $itemLine
     *
     * @return array
     */
    protected function applyUiPlugins(array $itemLine): array
    {
        foreach ($this->salesTablePlugins as $uiPlugin) {
            $itemLine = $uiPlugin->formatTableRow([$this, 'buttonGeneratorCallable'], $itemLine);
        }

        return $itemLine;
    }

    /**
     * @param \Spryker\Service\UtilText\Model\Url\Url|string $url
     * @param string $title
     * @param array<string, mixed> $options
     *
     * @return string
     */
    public function buttonGeneratorCallable($url, $title, array $options)
    {
        return $this->generateButton($url, $title, $options);
    }

    /**
     * @return string
     */
    protected function getFullNameSearchableField(): string
    {
        return sprintf(
            static::FULL_NAME_SEARCHABLE_FIELD_PATTERN,
            SpySalesOrderTableMap::COL_FIRST_NAME,
            static::COLUMN_SEPARATOR,
            SpySalesOrderTableMap::COL_LAST_NAME,
        );
    }
}
