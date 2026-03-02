<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Persistence;

use Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateHistoryQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateQuery;
use Orm\Zed\Sales\Persistence\SpySalesExpenseQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddressQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderCommentQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderTotalsQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesExpenseMapper;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesExpenseMapperInterface;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderAddressMapper;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderAddressMapperInterface;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderItemMapper;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderItemMapperInterface;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderMapper;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderTotalsMapper;
use Spryker\Zed\Sales\Persistence\Propel\QueryBuilder\OrderSearchFilterFieldQueryBuilder;
use Spryker\Zed\Sales\Persistence\Propel\QueryBuilder\OrderSearchFilterFieldQueryBuilderInterface;
use Spryker\Zed\Sales\Persistence\Propel\QueryBuilder\OrderSearchQueryJoinQueryBuilder;
use Spryker\Zed\Sales\Persistence\Propel\QueryBuilder\OrderSearchQueryJoinQueryBuilderInterface;
use Spryker\Zed\Sales\SalesDependencyProvider;

/**
 * @method \Spryker\Zed\Sales\SalesConfig getConfig()
 * @method \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Sales\Persistence\SalesEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\Sales\Persistence\SalesRepositoryInterface getRepository()
 */
class SalesPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderQuery
     */
    public function createSalesOrderQuery()
    {
        return SpySalesOrderQuery::create();
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery
     */
    public function createSalesOrderItemQuery()
    {
        return SpySalesOrderItemQuery::create();
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesExpenseQuery
     */
    public function createSalesExpenseQuery()
    {
        return SpySalesExpenseQuery::create();
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderAddressQuery
     */
    public function createSalesOrderAddressQuery()
    {
        return SpySalesOrderAddressQuery::create();
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderCommentQuery
     */
    public function createSalesOrderCommentQuery()
    {
        return SpySalesOrderCommentQuery::create();
    }

    public function getSalesOrderTotalsPropelQuery(): SpySalesOrderTotalsQuery
    {
        return SpySalesOrderTotalsQuery::create();
    }

    /**
     * @module Oms
     *
     * @return \Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateHistoryQuery
     */
    public function createOmsOrderItemStateHistoryQuery()
    {
        return SpyOmsOrderItemStateHistoryQuery::create();
    }

    public function createSalesExpenseMapper(): SalesExpenseMapperInterface
    {
        return new SalesExpenseMapper();
    }

    public function createSalesOrderAddressMapper(): SalesOrderAddressMapperInterface
    {
        return new SalesOrderAddressMapper();
    }

    public function createSalesOrderMapper(): SalesOrderMapper
    {
        return new SalesOrderMapper();
    }

    public function createSalesOrderTotalsMapper(): SalesOrderTotalsMapper
    {
        return new SalesOrderTotalsMapper();
    }

    public function createSalesOrderItemMapper(): SalesOrderItemMapperInterface
    {
        return new SalesOrderItemMapper();
    }

    public function createOrderSearchFilterFieldQueryBuilder(): OrderSearchFilterFieldQueryBuilderInterface
    {
        return new OrderSearchFilterFieldQueryBuilder();
    }

    public function createOrderSearchQueryJoinQueryBuilder(): OrderSearchQueryJoinQueryBuilderInterface
    {
        return new OrderSearchQueryJoinQueryBuilder();
    }

    public function getSalesQueryContainer(): SalesQueryContainerInterface
    {
        return $this->getQueryContainer();
    }

    public function getOmsOrderItemStatePropelQuery(): SpyOmsOrderItemStateQuery
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PROPEL_QUERY_OMS_ORDER_ITEM_STATE);
    }
}
