<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace Spryker\Zed\Sales\Business;

use Orm\Zed\Locale\Persistence\SpyLocaleQuery;
use Spryker\Shared\CheckoutExtension\CheckoutExtensionContextsInterface;
use Spryker\Shared\Kernel\StrategyResolver;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use Spryker\Shared\SalesOrderAmendmentExtension\SalesOrderAmendmentExtensionContextsInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Sales\Business\Address\OrderAddressWriter;
use Spryker\Zed\Sales\Business\Address\OrderAddressWriterInterface;
use Spryker\Zed\Sales\Business\Checker\DuplicateOrderChecker;
use Spryker\Zed\Sales\Business\Checker\DuplicateOrderCheckerInterface;
use Spryker\Zed\Sales\Business\Creator\SalesOrderItemCreator;
use Spryker\Zed\Sales\Business\Creator\SalesOrderItemCreatorInterface;
use Spryker\Zed\Sales\Business\Deleter\SalesExpenseDeleter;
use Spryker\Zed\Sales\Business\Deleter\SalesExpenseDeleterInterface;
use Spryker\Zed\Sales\Business\Deleter\SalesOrderItemDeleter;
use Spryker\Zed\Sales\Business\Deleter\SalesOrderItemDeleterInterface;
use Spryker\Zed\Sales\Business\Expander\ItemCurrencyExpander;
use Spryker\Zed\Sales\Business\Expander\ItemCurrencyExpanderInterface;
use Spryker\Zed\Sales\Business\Expander\SalesAddressExpander;
use Spryker\Zed\Sales\Business\Expander\SalesAddressExpanderInterface;
use Spryker\Zed\Sales\Business\Expense\ExpenseUpdater;
use Spryker\Zed\Sales\Business\Expense\ExpenseUpdaterInterface;
use Spryker\Zed\Sales\Business\Expense\ExpenseWriter;
use Spryker\Zed\Sales\Business\Expense\ExpenseWriterInterface;
use Spryker\Zed\Sales\Business\ItemSaver\OrderItemsSaver;
use Spryker\Zed\Sales\Business\ItemSaver\OrderItemsSaverInterface;
use Spryker\Zed\Sales\Business\Mapper\SalesOrderMapper;
use Spryker\Zed\Sales\Business\Mapper\SalesOrderMapperInterface;
use Spryker\Zed\Sales\Business\Model\Address\OrderAddressUpdater;
use Spryker\Zed\Sales\Business\Model\Address\OrderAddressUpdaterInterface;
use Spryker\Zed\Sales\Business\Model\Comment\OrderCommentReader;
use Spryker\Zed\Sales\Business\Model\Comment\OrderCommentReaderInterface;
use Spryker\Zed\Sales\Business\Model\Comment\OrderCommentSaver;
use Spryker\Zed\Sales\Business\Model\Comment\OrderCommentSaverInterface;
use Spryker\Zed\Sales\Business\Model\Customer\CustomerOrderOverviewInterface;
use Spryker\Zed\Sales\Business\Model\Customer\CustomerOrderReader;
use Spryker\Zed\Sales\Business\Model\Customer\CustomerOrderReaderInterface;
use Spryker\Zed\Sales\Business\Model\Customer\OffsetPaginatedCustomerOrderListReader;
use Spryker\Zed\Sales\Business\Model\Customer\OffsetPaginatedCustomerOrderListReaderInterface;
use Spryker\Zed\Sales\Business\Model\Customer\PaginatedCustomerOrderOverview;
use Spryker\Zed\Sales\Business\Model\Customer\PaginatedCustomerOrderReader;
use Spryker\Zed\Sales\Business\Model\Order\CustomerOrderOverviewHydrator;
use Spryker\Zed\Sales\Business\Model\Order\CustomerOrderOverviewHydratorInterface;
use Spryker\Zed\Sales\Business\Model\Order\OrderExpander;
use Spryker\Zed\Sales\Business\Model\Order\OrderExpanderInterface;
use Spryker\Zed\Sales\Business\Model\Order\OrderHydrator;
use Spryker\Zed\Sales\Business\Model\Order\OrderHydratorInterface as OrderOrderHydratorInterface;
use Spryker\Zed\Sales\Business\Model\Order\OrderReferenceGeneratorInterface;
use Spryker\Zed\Sales\Business\Model\Order\OrderRepositoryReader;
use Spryker\Zed\Sales\Business\Model\Order\OrderSaver;
use Spryker\Zed\Sales\Business\Model\Order\OrderSaverInterface;
use Spryker\Zed\Sales\Business\Model\Order\OrderUpdater;
use Spryker\Zed\Sales\Business\Model\Order\OrderUpdaterInterface;
use Spryker\Zed\Sales\Business\Model\Order\SalesOrderSaver;
use Spryker\Zed\Sales\Business\Model\Order\SalesOrderSaverInterface;
use Spryker\Zed\Sales\Business\Model\Order\SalesOrderSaverPluginExecutor;
use Spryker\Zed\Sales\Business\Model\Order\SalesOrderSaverPluginExecutorInterface;
use Spryker\Zed\Sales\Business\Model\Order\SequenceNumberOrderReferenceGenerator;
use Spryker\Zed\Sales\Business\Model\Order\UniqueRandomIdOrderReferenceGenerator;
use Spryker\Zed\Sales\Business\Model\OrderItem\OrderItemTransformer;
use Spryker\Zed\Sales\Business\Model\OrderItem\OrderItemTransformerInterface;
use Spryker\Zed\Sales\Business\Model\OrderItem\SalesOrderItemMapper as ModelSalesOrderItemMapper;
use Spryker\Zed\Sales\Business\Model\OrderItem\SalesOrderItemMapperInterface as OrderItemSalesOrderItemMapperInterface;
use Spryker\Zed\Sales\Business\Order\OrderHydrator as OrderHydratorWithMultiShippingAddress;
use Spryker\Zed\Sales\Business\Order\OrderHydratorInterface;
use Spryker\Zed\Sales\Business\Order\OrderReader;
use Spryker\Zed\Sales\Business\Order\OrderReaderInterface;
use Spryker\Zed\Sales\Business\OrderItem\SalesOrderItemGrouper;
use Spryker\Zed\Sales\Business\OrderItem\SalesOrderItemGrouperInterface;
use Spryker\Zed\Sales\Business\OrderWriter\SalesOrderWriter;
use Spryker\Zed\Sales\Business\OrderWriter\SalesOrderWriterInterface;
use Spryker\Zed\Sales\Business\Reader\OrderItemReader;
use Spryker\Zed\Sales\Business\Reader\OrderItemReaderInterface;
use Spryker\Zed\Sales\Business\Reader\OrderReader as SalesOrderReader;
use Spryker\Zed\Sales\Business\Reader\OrderReaderInterface as SalesOrderReaderInterface;
use Spryker\Zed\Sales\Business\SearchReader\OrderSearchReader;
use Spryker\Zed\Sales\Business\SearchReader\OrderSearchReaderInterface;
use Spryker\Zed\Sales\Business\StateMachineResolver\OrderStateMachineResolver;
use Spryker\Zed\Sales\Business\StateMachineResolver\OrderStateMachineResolverInterface;
use Spryker\Zed\Sales\Business\StrategyResolver\OrderHydratorStrategyResolver;
use Spryker\Zed\Sales\Business\StrategyResolver\OrderHydratorStrategyResolverInterface;
use Spryker\Zed\Sales\Business\Triggerer\OmsEventTriggerer;
use Spryker\Zed\Sales\Business\Triggerer\OmsEventTriggererInterface;
use Spryker\Zed\Sales\Business\Updater\SalesOrderAddressUpdater;
use Spryker\Zed\Sales\Business\Updater\SalesOrderAddressUpdaterInterface;
use Spryker\Zed\Sales\Business\Updater\SalesOrderItemUpdater;
use Spryker\Zed\Sales\Business\Updater\SalesOrderItemUpdaterInterface;
use Spryker\Zed\Sales\Business\Updater\SalesOrderUpdater;
use Spryker\Zed\Sales\Business\Updater\SalesOrderUpdaterInterface;
use Spryker\Zed\Sales\Business\Validator\Rule\SalesOrderItem\DuplicationSalesOrderItemValidatorRule;
use Spryker\Zed\Sales\Business\Validator\Rule\SalesOrderItem\ExistenceSalesOrderItemValidatorRule;
use Spryker\Zed\Sales\Business\Validator\Rule\SalesOrderItem\SalesOrderItemRelationValidatorRule;
use Spryker\Zed\Sales\Business\Validator\Rule\SalesOrderItem\SalesOrderItemValidatorRuleInterface;
use Spryker\Zed\Sales\Business\Validator\SalesOrderItemValidator;
use Spryker\Zed\Sales\Business\Validator\SalesOrderItemValidatorInterface;
use Spryker\Zed\Sales\Business\Validator\Util\ErrorAdder;
use Spryker\Zed\Sales\Business\Validator\Util\ErrorAdderInterface;
use Spryker\Zed\Sales\Business\Writer\OrderWriter;
use Spryker\Zed\Sales\Business\Writer\OrderWriterInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCalculationInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCountryInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCustomerInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToLocaleInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToOmsInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToSequenceNumberInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToStoreInterface;
use Spryker\Zed\Sales\Dependency\Service\SalesToUtilUuidGeneratorInterface;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderItemMapper;
use Spryker\Zed\Sales\Persistence\Propel\Mapper\SalesOrderItemMapperInterface;
use Spryker\Zed\Sales\SalesDependencyProvider;

/**
 * @method \Spryker\Zed\Sales\SalesConfig getConfig()
 * @method \Spryker\Zed\Sales\Persistence\SalesRepositoryInterface getRepository()
 * @method \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Sales\Persistence\SalesEntityManagerInterface getEntityManager()
 */
class SalesBusinessFactory extends AbstractBusinessFactory
{
    public function createCustomerOrderReader(): CustomerOrderReaderInterface
    {
        return new CustomerOrderReader(
            $this->getQueryContainer(),
            $this->createOrderHydratorStrategyResolver(),
            $this->getSearchOrderExpanderPlugins(),
            $this->getOmsFacade(),
        );
    }

    public function createPaginatedCustomerOrderReader(): CustomerOrderReaderInterface
    {
        return new PaginatedCustomerOrderReader(
            $this->getQueryContainer(),
            $this->createOrderHydratorStrategyResolver(),
            $this->getSearchOrderExpanderPlugins(),
            $this->getOmsFacade(),
        );
    }

    public function createOffsetPaginatedCustomerOrderListReader(): OffsetPaginatedCustomerOrderListReaderInterface
    {
        return new OffsetPaginatedCustomerOrderListReader(
            $this->getRepository(),
            $this->createOrderHydrator(),
            $this->getOmsFacade(),
            $this->getSearchOrderExpanderPlugins(),
        );
    }

    public function createPaginatedCustomerOrderOverview(): CustomerOrderOverviewInterface
    {
        return new PaginatedCustomerOrderOverview(
            $this->getQueryContainer(),
            $this->createCustomerOrderOverviewHydrator(),
            $this->getOmsFacade(),
            $this->getSearchOrderExpanderPlugins(),
            $this->getCustomerFacade(),
        );
    }

    public function createCustomerOrderOverviewHydrator(): CustomerOrderOverviewHydratorInterface
    {
        return new CustomerOrderOverviewHydrator();
    }

    /**
     * @deprecated Use {@link createSalesOrderSaver()} instead.
     *
     * @return \Spryker\Zed\Sales\Business\Model\Order\OrderSaverInterface
     */
    public function createOrderSaver(): OrderSaverInterface
    {
        return new OrderSaver(
            $this->getCountryFacade(),
            $this->getOmsFacade(),
            $this->createReferenceGenerator(),
            $this->getConfig(),
            $this->getLocalePropelQuery(),
            $this->getOrderExpanderPreSavePlugins(),
            $this->createSalesOrderSaverPluginExecutor(),
            $this->createSalesOrderItemMapper(),
            $this->createOrderPostSavePluginStrategyResolver(),
            $this->getStoreFacade(),
            $this->getLocaleFacade(),
            $this->createOrderStateMachineResolver(),
        );
    }

    public function createSalesOrderSaver(): SalesOrderSaverInterface
    {
        return new SalesOrderSaver(
            $this->getCountryFacade(),
            $this->getOmsFacade(),
            $this->createReferenceGenerator(),
            $this->getConfig(),
            $this->getLocalePropelQuery(),
            $this->getOrderExpanderPreSavePlugins(),
            $this->createSalesOrderSaverPluginExecutor(),
            $this->createSalesOrderItemMapper(),
            $this->createOrderPostSavePluginStrategyResolver(),
            $this->getStoreFacade(),
            $this->getLocaleFacade(),
            $this->createOrderStateMachineResolver(),
        );
    }

    public function createSalesOrderWriter(): SalesOrderWriterInterface
    {
        return new SalesOrderWriter(
            $this->getCountryFacade(),
            $this->getStoreFacade(),
            $this->createReferenceGenerator(),
            $this->getConfig(),
            $this->getLocaleFacade(),
            $this->getOrderExpanderPreSavePlugins(),
            $this->createOrderPostSavePluginStrategyResolver(),
            $this->getEntityManager(),
        );
    }

    public function createOrderItemsSaver(): OrderItemsSaverInterface
    {
        return new OrderItemsSaver(
            $this->getOmsFacade(),
            $this->createSalesOrderSaverPluginExecutor(),
            $this->getEntityManager(),
            $this->getOrderItemsPostSavePlugins(),
            $this->createOrderStateMachineResolver(),
            $this->createOrderItemInitialStateProviderPluginStrategyResolver(),
            $this->getConfig(),
        );
    }

    public function createOrderUpdater(): OrderUpdaterInterface
    {
        return new OrderUpdater(
            $this->getQueryContainer(),
            $this->getConfig(),
            $this->getOrderPostUpdatePlugins(),
        );
    }

    public function createSalesOrderUpdater(): SalesOrderUpdaterInterface
    {
        return new SalesOrderUpdater(
            $this->getEntityManager(),
            $this->createSalesOrderMapper(),
            $this->createSalesOrderAddressUpdater(),
            $this->getConfig(),
            $this->getOrderExpanderPreSavePlugins(),
            $this->createOrderPostSavePluginStrategyResolver(),
            $this->getLocaleFacade(),
        );
    }

    public function createSalesOrderAddressUpdater(): SalesOrderAddressUpdaterInterface
    {
        return new SalesOrderAddressUpdater($this->getEntityManager(), $this->getCountryFacade());
    }

    public function createOrderReader(): OrderReaderInterface
    {
        return new OrderReader(
            $this->getQueryContainer(),
            $this->createOrderHydratorWithMultiShippingAddress(),
        );
    }

    public function createOrderRepositoryReader(): OrderRepositoryReader
    {
        return new OrderRepositoryReader(
            $this->createOrderHydratorStrategyResolver(),
            $this->getRepository(),
        );
    }

    public function createOrderCommentReader(): OrderCommentReaderInterface
    {
        return new OrderCommentReader($this->getQueryContainer());
    }

    public function createOrderCommentSaver(): OrderCommentSaverInterface
    {
        return new OrderCommentSaver($this->getQueryContainer());
    }

    /**
     * @deprecated Use {@link createOrderHydratorWithMultiShippingAddress()} instead.
     *
     * @return \Spryker\Zed\Sales\Business\Model\Order\OrderHydratorInterface
     */
    public function createOrderHydrator(): OrderOrderHydratorInterface
    {
        return new OrderHydrator(
            $this->getQueryContainer(),
            $this->getOmsFacade(),
            $this->getConfig(),
            $this->getCustomerFacade(),
            $this->getHydrateOrderPlugins(),
            $this->getOrderItemExpanderPlugins(),
            $this->getCustomerOrderAccessCheckPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\Sales\Business\Model\Order\OrderHydratorInterface
     */
    public function createOrderHydratorWithMultiShippingAddress(): OrderHydratorInterface
    {
        return new OrderHydratorWithMultiShippingAddress(
            $this->getQueryContainer(),
            $this->getOmsFacade(),
            $this->getConfig(),
            $this->getCustomerFacade(),
            $this->getHydrateOrderPlugins(),
            $this->getOrderItemExpanderPlugins(),
            $this->getCustomerOrderAccessCheckPlugins(),
        );
    }

    public function createSalesOrderReader(): SalesOrderReaderInterface
    {
        return new SalesOrderReader(
            $this->getRepository(),
            $this->getHydrateOrderPlugins(),
        );
    }

    public function createReferenceGenerator(): OrderReferenceGeneratorInterface
    {
        if ($this->getConfig()->useUniqueRandomIdOrderReferenceGenerator()) {
            return $this->createUniqueRandomIdOrderReferenceGenerator();
        }

        return $this->createSequenceNumberOrderReferenceGenerator();
    }

    public function createSequenceNumberOrderReferenceGenerator(): OrderReferenceGeneratorInterface
    {
        return new SequenceNumberOrderReferenceGenerator(
            $this->getSequenceNumberFacade(),
            $this->getConfig(),
            $this->getStoreFacade(),
        );
    }

    public function createUniqueRandomIdOrderReferenceGenerator(): OrderReferenceGeneratorInterface
    {
        return new UniqueRandomIdOrderReferenceGenerator(
            $this->getUtilUuidGeneratorService(),
            $this->getConfig(),
            $this->getStoreFacade(),
        );
    }

    /**
     * @deprecated Use {@link createOrderAddressWriter()} instead.
     *
     * @return \Spryker\Zed\Sales\Business\Model\Address\OrderAddressUpdaterInterface
     */
    public function createOrderAddressUpdater(): OrderAddressUpdaterInterface
    {
        return new OrderAddressUpdater($this->getQueryContainer());
    }

    public function createOrderAddressWriter(): OrderAddressWriterInterface
    {
        return new OrderAddressWriter($this->getEntityManager(), $this->getCountryFacade());
    }

    public function createSalesAddressExpander(): SalesAddressExpanderInterface
    {
        return new SalesAddressExpander($this->getCustomerFacade(), $this->getRepository());
    }

    public function createOrderExpander(): OrderExpanderInterface
    {
        return new OrderExpander(
            $this->getCalculationFacade(),
            $this->createOrderItemTransformer(),
            $this->getItemTransformerStrategyPlugins(),
            $this->getItemPreTransformerPlugins(),
        );
    }

    public function createOrderItemTransformer(): OrderItemTransformerInterface
    {
        return new OrderItemTransformer();
    }

    public function createSalesOrderSaverPluginExecutor(): SalesOrderSaverPluginExecutorInterface
    {
        return new SalesOrderSaverPluginExecutor(
            $this->getOrderItemExpanderPreSavePlugins(),
        );
    }

    /**
     * @deprecated Use {@link createSalesOrderItemMapper()} instead.
     *
     * @return \Spryker\Zed\Sales\Business\Model\OrderItem\SalesOrderItemMapperInterface
     */
    public function createOrderItemMapper(): OrderItemSalesOrderItemMapperInterface
    {
        return new ModelSalesOrderItemMapper();
    }

    public function createSalesOrderMapper(): SalesOrderMapperInterface
    {
        return new SalesOrderMapper();
    }

    public function createExpenseWriter(): ExpenseWriterInterface
    {
        return new ExpenseWriter($this->getEntityManager());
    }

    public function createExpenseUpdater(): ExpenseUpdaterInterface
    {
        return new ExpenseUpdater($this->getEntityManager());
    }

    public function createSalesOrderItemGrouper(): SalesOrderItemGrouperInterface
    {
        return new SalesOrderItemGrouper(
            $this->getUniqueOrderItemsExpanderPlugins(),
        );
    }

    public function createSalesOrderItemMapper(): SalesOrderItemMapperInterface
    {
        return new SalesOrderItemMapper();
    }

    public function createOrderSearchReader(): OrderSearchReaderInterface
    {
        return new OrderSearchReader(
            $this->getRepository(),
            $this->getSearchOrderExpanderPlugins(),
            $this->getOrderSearchQueryExpanderPlugins(),
        );
    }

    public function createItemCurrencyExpander(): ItemCurrencyExpanderInterface
    {
        return new ItemCurrencyExpander($this->getRepository());
    }

    public function getCalculationFacade(): SalesToCalculationInterface
    {
        return $this->getProvidedDependency(SalesDependencyProvider::FACADE_CALCULATION);
    }

    public function getSequenceNumberFacade(): SalesToSequenceNumberInterface
    {
        return $this->getProvidedDependency(SalesDependencyProvider::FACADE_SEQUENCE_NUMBER);
    }

    public function getOmsFacade(): SalesToOmsInterface
    {
        return $this->getProvidedDependency(SalesDependencyProvider::FACADE_OMS);
    }

    public function getCountryFacade(): SalesToCountryInterface
    {
        return $this->getProvidedDependency(SalesDependencyProvider::FACADE_COUNTRY);
    }

    public function getLocalePropelQuery(): SpyLocaleQuery
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PROPEL_QUERY_LOCALE);
    }

    public function getStoreFacade(): SalesToStoreInterface
    {
        return $this->getProvidedDependency(SalesDependencyProvider::FACADE_STORE);
    }

    public function getLocaleFacade(): SalesToLocaleInterface
    {
        return $this->getProvidedDependency(SalesDependencyProvider::FACADE_LOCALE);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderExpanderPluginInterface>
     */
    public function getHydrateOrderPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::HYDRATE_ORDER_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderExpanderPreSavePluginInterface>
     */
    public function getOrderExpanderPreSavePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::ORDER_EXPANDER_PRE_SAVE_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderItemExpanderPreSavePluginInterface>
     */
    public function getOrderItemExpanderPreSavePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::ORDER_ITEM_EXPANDER_PRE_SAVE_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\ItemTransformerStrategyPluginInterface>
     */
    public function getItemTransformerStrategyPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::ITEM_TRANSFORMER_STRATEGY_PLUGINS);
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<list<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderPostSavePluginInterface>>
     */
    public function createOrderPostSavePluginStrategyResolver(): StrategyResolverInterface
    {
        return new StrategyResolver(
            [
                CheckoutExtensionContextsInterface::CONTEXT_CHECKOUT => $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_POST_SAVE, static::LOADING_LAZY),
                SalesOrderAmendmentExtensionContextsInterface::CONTEXT_ORDER_AMENDMENT => $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_POST_SAVE_FOR_ORDER_AMENDMENT, static::LOADING_LAZY),
                SalesOrderAmendmentExtensionContextsInterface::CONTEXT_ORDER_AMENDMENT_ASYNC => $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_POST_SAVE_FOR_ORDER_AMENDMENT_ASYNC, static::LOADING_LAZY),
            ],
            CheckoutExtensionContextsInterface::CONTEXT_CHECKOUT,
        );
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<list<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderItemInitialStateProviderPluginInterface>>
     */
    public function createOrderItemInitialStateProviderPluginStrategyResolver(): StrategyResolverInterface
    {
        return new StrategyResolver(
            [
                CheckoutExtensionContextsInterface::CONTEXT_CHECKOUT => $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEM_INITIAL_STATE_PROVIDER, static::LOADING_LAZY),
                SalesOrderAmendmentExtensionContextsInterface::CONTEXT_ORDER_AMENDMENT => $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEM_INITIAL_STATE_PROVIDER_FOR_ORDER_AMENDMENT, static::LOADING_LAZY),
                SalesOrderAmendmentExtensionContextsInterface::CONTEXT_ORDER_AMENDMENT_ASYNC => $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEM_INITIAL_STATE_PROVIDER_FOR_ORDER_AMENDMENT_ASYNC, static::LOADING_LAZY),
            ],
            CheckoutExtensionContextsInterface::CONTEXT_CHECKOUT,
        );
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only. Use $this->createOrderHydratorWithMultiShippingAddress() instead.
     *
     * @return \Spryker\Zed\Sales\Business\StrategyResolver\OrderHydratorStrategyResolverInterface
     */
    public function createOrderHydratorStrategyResolver(): OrderHydratorStrategyResolverInterface
    {
        $strategyContainer = [];

        $strategyContainer[OrderHydratorStrategyResolver::STRATEGY_KEY_WITHOUT_MULTI_SHIPMENT] = function () {
            return $this->createOrderHydrator();
        };

        $strategyContainer[OrderHydratorStrategyResolver::STRATEGY_KEY_WITH_MULTI_SHIPMENT] = function () {
            return $this->createOrderHydratorWithMultiShippingAddress();
        };

        return new OrderHydratorStrategyResolver($strategyContainer);
    }

    public function createOrderItemReader(): OrderItemReaderInterface
    {
        return new OrderItemReader(
            $this->getRepository(),
            $this->getOrderItemExpanderPlugins(),
        );
    }

    public function createOrderWriter(): OrderWriterInterface
    {
        return new OrderWriter(
            $this->createOmsEventTriggerer(),
            $this->createOrderReader(),
            $this->getRepository(),
            $this->getOrderPostCancelPlugins(),
        );
    }

    public function createOmsEventTriggerer(): OmsEventTriggererInterface
    {
        return new OmsEventTriggerer($this->getOmsFacade());
    }

    public function createDuplicateOrderChecker(): DuplicateOrderCheckerInterface
    {
        return new DuplicateOrderChecker(
            $this->getRepository(),
        );
    }

    public function createSalesExpenseDeleter(): SalesExpenseDeleterInterface
    {
        return new SalesExpenseDeleter(
            $this->getRepository(),
            $this->getEntityManager(),
            $this->getSalesExpensePreDeletePlugins(),
        );
    }

    public function createSalesOrderItemCreator(): SalesOrderItemCreatorInterface
    {
        return new SalesOrderItemCreator(
            $this->getEntityManager(),
            $this->createSalesOrderItemCreateValidator(),
            $this->createOrderItemsSaver(),
            $this->getsalesOrderItemsPreCreatePlugins(),
            $this->getSalesOrderItemCollectionPostCreatePlugins(),
        );
    }

    public function createSalesOrderItemUpdater(): SalesOrderItemUpdaterInterface
    {
        return new SalesOrderItemUpdater(
            $this->getEntityManager(),
            $this->createSalesOrderItemUpdateValidator(),
            $this->createOrderItemsSaver(),
            $this->getsalesOrderItemsPreUpdatePlugins(),
            $this->getSalesOrderItemCollectionPostUpdatePlugins(),
        );
    }

    public function createSalesOrderItemCreateValidator(): SalesOrderItemValidatorInterface
    {
        return new SalesOrderItemValidator(
            $this->getSalesOrderItemCreateValidatorRule(),
        );
    }

    public function createSalesOrderItemUpdateValidator(): SalesOrderItemValidatorInterface
    {
        return new SalesOrderItemValidator(
            $this->getSalesOrderItemUpdateValidatorRule(),
        );
    }

    public function createSalesOrderItemDeleter(): SalesOrderItemDeleterInterface
    {
        return new SalesOrderItemDeleter(
            $this->getEntityManager(),
            $this->getSalesOrderItemCollectionPreDeletePlugins(),
        );
    }

    /**
     * @return list<\Spryker\Zed\Sales\Business\Validator\Rule\SalesOrderItem\SalesOrderItemValidatorRuleInterface>
     */
    public function getSalesOrderItemCreateValidatorRule(): array
    {
        return [
            $this->createSalesOrderItemRelationValidatorRule(),
        ];
    }

    /**
     * @return list<\Spryker\Zed\Sales\Business\Validator\Rule\SalesOrderItem\SalesOrderItemValidatorRuleInterface>
     */
    public function getSalesOrderItemUpdateValidatorRule(): array
    {
        return [
            $this->createSalesOrderItemRelationValidatorRule(),
            $this->createExistenceSalesOrderItemValidatorRule(),
            $this->createDuplicationSalesOrderItemValidatorRule(),
        ];
    }

    public function createSalesOrderItemRelationValidatorRule(): SalesOrderItemValidatorRuleInterface
    {
        return new SalesOrderItemRelationValidatorRule(
            $this->createErrorAdder(),
            $this->getRepository(),
        );
    }

    public function createExistenceSalesOrderItemValidatorRule(): SalesOrderItemValidatorRuleInterface
    {
        return new ExistenceSalesOrderItemValidatorRule(
            $this->createErrorAdder(),
            $this->getRepository(),
        );
    }

    public function createDuplicationSalesOrderItemValidatorRule(): SalesOrderItemValidatorRuleInterface
    {
        return new DuplicationSalesOrderItemValidatorRule(
            $this->createErrorAdder(),
        );
    }

    public function createErrorAdder(): ErrorAdderInterface
    {
        return new ErrorAdder();
    }

    public function getCustomerFacade(): SalesToCustomerInterface
    {
        return $this->getProvidedDependency(SalesDependencyProvider::FACADE_CUSTOMER);
    }

    public function getUtilUuidGeneratorService(): SalesToUtilUuidGeneratorInterface
    {
        return $this->getProvidedDependency(SalesDependencyProvider::SERVICE_UTIL_UUID_GENERATOR);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\ItemPreTransformerPluginInterface>
     */
    public function getItemPreTransformerPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ITEM_PRE_TRANSFORMER);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\UniqueOrderItemsExpanderPluginInterface>
     */
    public function getUniqueOrderItemsExpanderPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_UNIQUE_ORDER_ITEMS_EXPANDER);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderItemExpanderPluginInterface>
     */
    public function getOrderItemExpanderPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEM_EXPANDER);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\SearchOrderExpanderPluginInterface>
     */
    public function getSearchOrderExpanderPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_SEARCH_ORDER_EXPANDER);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\SearchOrderQueryExpanderPluginInterface>
     */
    public function getOrderSearchQueryExpanderPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_SEARCH_QUERY_EXPANDER);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\CustomerOrderAccessCheckPluginInterface>
     */
    public function getCustomerOrderAccessCheckPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_CUSTOMER_ORDER_ACCESS_CHECK);
    }

    /**
     * @return array<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderItemsPostSavePluginInterface>
     */
    public function getOrderItemsPostSavePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEMS_POST_SAVE);
    }

    /**
     * @return list<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderPostUpdatePluginInterface>
     */
    public function getOrderPostUpdatePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_POST_UPDATE);
    }

    public function createOrderStateMachineResolver(): OrderStateMachineResolverInterface
    {
        return new OrderStateMachineResolver($this->getConfig());
    }

    /**
     * @return list<\Spryker\Zed\SalesExtension\Dependency\Plugin\OrderPostCancelPluginInterface>
     */
    public function getOrderPostCancelPlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_POST_CANCEL);
    }

    /**
     * @return list<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesExpensePreDeletePluginInterface>
     */
    public function getSalesExpensePreDeletePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_SALES_EXPENSE_PRE_DELETE);
    }

    /**
     * @return list<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesOrderItemCollectionPreDeletePluginInterface>
     */
    public function getSalesOrderItemCollectionPreDeletePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_SALES_ORDER_ITEM_COLLECTION_PRE_DELETE);
    }

    /**
     * @return list<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesOrderItemsPreCreatePluginInterface>
     */
    public function getSalesOrderItemsPreCreatePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEMS_PRE_CREATE);
    }

    /**
     * @return list<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesOrderItemCollectionPostCreatePluginInterface>
     */
    public function getSalesOrderItemCollectionPostCreatePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEM_COLLECTION_POST_CREATE);
    }

    /**
     * @return list<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesOrderItemsPreUpdatePluginInterface>
     */
    public function getSalesOrderItemsPreUpdatePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEMS_PRE_UPDATE);
    }

    /**
     * @return list<\Spryker\Zed\SalesExtension\Dependency\Plugin\SalesOrderItemCollectionPostUpdatePluginInterface>
     */
    public function getSalesOrderItemCollectionPostUpdatePlugins(): array
    {
        return $this->getProvidedDependency(SalesDependencyProvider::PLUGINS_ORDER_ITEM_COLLECTION_POST_UPDATE);
    }
}
