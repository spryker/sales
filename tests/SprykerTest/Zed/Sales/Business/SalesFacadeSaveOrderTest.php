<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Sales\Business;

use Codeception\Test\Unit;
use DateTime;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\TaxTotalTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Orm\Zed\Country\Persistence\SpyCountry;
use Orm\Zed\Locale\Persistence\SpyLocaleQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcess;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcessQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddressQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderTotalsQuery;
use Spryker\Shared\Price\PriceMode;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Locale\Business\LocaleFacade;
use Spryker\Zed\Oms\OmsConfig;
use Spryker\Zed\Sales\Business\SalesBusinessFactory;
use Spryker\Zed\Sales\Business\SalesFacade;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCountryBridge;
use Spryker\Zed\Sales\Dependency\Facade\SalesToCountryInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToLocaleBridge;
use Spryker\Zed\Sales\Dependency\Facade\SalesToOmsBridge;
use Spryker\Zed\Sales\Dependency\Facade\SalesToOmsInterface;
use Spryker\Zed\Sales\Dependency\Facade\SalesToSequenceNumberBridge;
use Spryker\Zed\Sales\Dependency\Facade\SalesToStoreBridge;
use Spryker\Zed\Sales\SalesConfig;
use Spryker\Zed\Sales\SalesDependencyProvider;
use Spryker\Zed\SequenceNumber\Business\SequenceNumberFacade;
use Spryker\Zed\Store\Business\StoreFacade;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Sales
 * @group Business
 * @group Facade
 * @group SalesFacadeSaveOrderTest
 * Add your own group annotations below this line
 */
class SalesFacadeSaveOrderTest extends Unit
{
    /**
     * @var \Spryker\Zed\Sales\Business\SalesFacade
     */
    protected $salesFacade;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $countryFacadeMock = $this->getMockBuilder(SalesToCountryInterface::class)->setMethods(['getCountryByIso2Code', 'getAvailableCountries'])->getMock();
        $countryFacadeMock->method('getCountryByIso2Code')
            ->willReturn((new CountryTransfer())->setIdCountry(1));

        $omsOrderProcessEntity = $this->getProcessEntity();

        $omsFacadeMock = $this->getMockBuilder(SalesToOmsInterface::class)
            ->setMethods([
                'selectProcess',
                'getInitialStateEntity',
                'getProcessEntity',
                'getManualEvents',
                'getItemsWithFlag',
                'getManualEventsByIdSalesOrder',
                'getDistinctManualEventsByIdSalesOrder',
                'getGroupedDistinctManualEventsByIdSalesOrder',
                'getOrderItemMatrix',
                'isOrderFlaggedExcludeFromCustomer',
                'triggerEventForOrderItems',
            ])
            ->getMock();
        $omsFacadeMock->method('selectProcess')
            ->will($this->returnValue('CheckoutTest01'));

        $omcConfig = new OmsConfig();

        $initialStateEntity = SpyOmsOrderItemStateQuery::create()
            ->filterByName($omcConfig->getInitialStatus())
            ->findOneOrCreate();
        $initialStateEntity->save();

        $omsFacadeMock->method('getInitialStateEntity')
            ->will($this->returnValue($initialStateEntity));

        $omsFacadeMock->method('getProcessEntity')
            ->will($this->returnValue($omsOrderProcessEntity));

        $sequenceNumberFacade = new SequenceNumberFacade();

        $container = new Container();
        $container[SalesDependencyProvider::FACADE_COUNTRY] = new SalesToCountryBridge($countryFacadeMock);
        $container[SalesDependencyProvider::FACADE_OMS] = new SalesToOmsBridge($omsFacadeMock);
        $container[SalesDependencyProvider::FACADE_SEQUENCE_NUMBER] = new SalesToSequenceNumberBridge($sequenceNumberFacade);
        $container[SalesDependencyProvider::PROPEL_QUERY_LOCALE] = new SpyLocaleQuery();
        $container[SalesDependencyProvider::FACADE_STORE] = new SalesToStoreBridge(new StoreFacade());
        $container[SalesDependencyProvider::FACADE_LOCALE] = new SalesToLocaleBridge(new LocaleFacade());
        $container[SalesDependencyProvider::ORDER_EXPANDER_PRE_SAVE_PLUGINS] = [];
        $container[SalesDependencyProvider::PLUGINS_ORDER_POST_SAVE] = [];
        $container[SalesDependencyProvider::PLUGINS_ORDER_ITEM_EXPANDER] = [];
        $container[SalesDependencyProvider::ORDER_ITEM_EXPANDER_PRE_SAVE_PLUGINS] = function (Container $container) {
            return [];
        };
        $container[SalesDependencyProvider::PLUGINS_ORDER_ITEMS_POST_SAVE] = function () {
            return [];
        };

        $this->salesFacade = new SalesFacade();
        $businessFactory = new SalesBusinessFactory();
        $salesConfigMock = $this->getMockBuilder(SalesConfig::class)->setMethods(['determineProcessForOrderItem'])->getMock();
        $salesConfigMock->method('determineProcessForOrderItem')->willReturn('');
        $businessFactory->setConfig($salesConfigMock);
        $businessFactory->setContainer($container);
        $this->salesFacade->setFactory($businessFactory);
    }

    /**
     * @return void
     */
    public function testSaveOrderCreatesBillingAddressAndAssignsItToOrder(): void
    {
        $salesOrderAddressQuery = SpySalesOrderAddressQuery::create()
            ->filterByAddress1('address-1-1-test')
            ->filterByFirstName('Max')
            ->filterByLastName('Mustermann')
            ->filterByZipCode('1337')
            ->filterByCity('SpryHome');

        $quoteTransfer = $this->getValidBaseQuoteTransfer();

        $this->salesFacade->saveSalesOrder($quoteTransfer, $this->getValidBaseResponseTransfer()->getSaveOrder());

        $addressEntity = $salesOrderAddressQuery->findOne();

        $this->assertNotNull($addressEntity);
        $this->assertSame($addressEntity->getIdSalesOrderAddress(), $quoteTransfer->getBillingAddress()
            ->getIdSalesOrderAddress());
    }

    /**
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    private function getValidBaseResponseTransfer(): CheckoutResponseTransfer
    {
        return (new CheckoutResponseTransfer())
            ->setSaveOrder(new SaveOrderTransfer());
    }

    /**
     * @return \Generated\Shared\Transfer\SaveOrderTransfer
     */
    private function createSaveOrderTransfer(): SaveOrderTransfer
    {
        return new SaveOrderTransfer();
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    private function getValidBaseQuoteTransfer(): QuoteTransfer
    {
        $country = new SpyCountry();
        $country->setIso2Code('ix');
        $country->save();

        $quoteTransfer = new QuoteTransfer();
        $currencyTransfer = new CurrencyTransfer();
        $currencyTransfer->setCode('EUR');
        $quoteTransfer->setCurrency($currencyTransfer);

        $quoteTransfer->setPriceMode(PriceMode::PRICE_MODE_GROSS);
        $billingAddress = new AddressTransfer();

        $billingAddress->setIso2Code('ix')
            ->setAddress1('address-1-1-test')
            ->setFirstName('Max')
            ->setLastName('Mustermann')
            ->setZipCode('1337')
            ->setCity('SpryHome');

        $shippingAddress = new AddressTransfer();
        $shippingAddress->setIso2Code('ix')
            ->setAddress1('address-1-2-test')
            ->setFirstName('Max')
            ->setLastName('Mustermann')
            ->setZipCode('1337')
            ->setCity('SpryHome');

        $totals = new TotalsTransfer();
        $totals->setGrandTotal(1337)
            ->setSubtotal(337);

        $totals->setTaxTotal((new TaxTotalTransfer())->setAmount(10));

        $quoteTransfer->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
            ->setTotals($totals);

        $customerTransfer = new CustomerTransfer();
        $customerTransfer->setEmail('max@mustermann.de');
        $customerTransfer->setFirstName('Max');
        $customerTransfer->setLastName('Mustermann');

        $quoteTransfer->setCustomer($customerTransfer);

        $shipmentTransfer = new ShipmentTransfer();
        $shipmentTransfer->setMethod(new ShipmentMethodTransfer());
        $shipmentTransfer->setShippingAddress($shippingAddress);
        $quoteTransfer->setShipment($shipmentTransfer);

        $itemTransfer = new ItemTransfer();
        $itemTransfer
            ->setUnitPrice(1)
            ->setUnitGrossPrice(1)
            ->setSumGrossPrice(1)
            ->setQuantity(1)
            ->setName('test-name')
            ->setSku('sku-test')
            ->setShipment($shipmentTransfer);
        $quoteTransfer->addItem($itemTransfer);

        $paymentTransfer = new PaymentTransfer();
        $paymentTransfer->setPaymentSelection('dummyPaymentInvoice');

        $quoteTransfer->setPayment($paymentTransfer);

        return $quoteTransfer;
    }

    /**
     * @return void
     */
    public function testSaveOrderCreatesShippingAddressAndAssignsItToOrder(): void
    {
        $salesOrderAddressQuery = SpySalesOrderAddressQuery::create()
            ->filterByAddress1('address-1-2-test')
            ->filterByFirstName('Max')
            ->filterByLastName('Mustermann')
            ->filterByCity('SpryHome');

        $quoteTransfer = $this->getValidBaseQuoteTransfer();

        $this->salesFacade->saveSalesOrder($quoteTransfer, $this->getValidBaseResponseTransfer()->getSaveOrder());

        $addressEntity = $salesOrderAddressQuery->findOne();

        $this->assertNotNull($addressEntity);
        $this->assertSame($addressEntity->getIdSalesOrderAddress(), $quoteTransfer->getShippingAddress()
            ->getIdSalesOrderAddress());
    }

    /**
     * @return void
     */
    public function testSaveOrderAssignsSavedOrderId(): void
    {
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $checkoutResponseTransfer = $this->getValidBaseResponseTransfer();
        $this->salesFacade->saveSalesOrder($quoteTransfer, $checkoutResponseTransfer->getSaveOrder());

        $this->assertNotNull($checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder());
    }

    /**
     * @return void
     */
    public function testSaveOrderCreatesOrderAndSavesFieldsDeprecated(): void
    {
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $checkoutResponseTransfer = $this->getValidBaseResponseTransfer();
        $this->salesFacade->saveSalesOrder($quoteTransfer, $checkoutResponseTransfer->getSaveOrder());

        $orderQuery = SpySalesOrderQuery::create()
            ->filterByPrimaryKey($checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder());

        $orderEntity = $orderQuery->findOne();
        $this->assertNotNull($orderEntity);

        $this->assertSame('max@mustermann.de', $orderEntity->getEmail());
        $this->assertSame('Max', $orderEntity->getFirstName());
        $this->assertSame('Mustermann', $orderEntity->getLastName());
    }

    /**
     * @return void
     */
    public function testSaveOrderCreatesOrderAndSavesFields(): void
    {
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $saveOrderTransfer = $this->createSaveOrderTransfer();
        $this->salesFacade->saveSalesOrder($quoteTransfer, $saveOrderTransfer);

        $orderQuery = SpySalesOrderQuery::create()
            ->filterByPrimaryKey($saveOrderTransfer->getIdSalesOrder());

        $orderEntity = $orderQuery->findOne();
        $this->assertNotNull($orderEntity);

        $this->assertSame('max@mustermann.de', $orderEntity->getEmail());
        $this->assertSame('Max', $orderEntity->getFirstName());
        $this->assertSame('Mustermann', $orderEntity->getLastName());
    }

    /**
     * @return void
     */
    public function testSaveOrderWhenCustomerHaveCreatedAtSetShouldNotOverwriteOrderData(): void
    {
        $quoteTransfer = $this->getValidBaseQuoteTransfer();

        $customerCreatedAt = new DateTime('Yesterday');
        $quoteTransfer->getCustomer()->setCreatedAt($customerCreatedAt);

        $checkoutResponseTransfer = $this->getValidBaseResponseTransfer();
        $this->salesFacade->saveSalesOrder($quoteTransfer, $checkoutResponseTransfer->getSaveOrder());

        $orderQuery = SpySalesOrderQuery::create()
            ->filterByPrimaryKey($checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder());

        $orderEntity = $orderQuery->findOne();
        $this->assertNotNull($orderEntity);

        $this->assertNotEquals($customerCreatedAt->format('Y-m-d'), $orderEntity->getCreatedAt('Y-m-d'), 'Dates are not expected to be equal.');
    }

    /**
     * @return void
     */
    public function testSaveOrderCreatesAndFillsOrderItems(): void
    {
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $omsConfig = new OmsConfig();

        $initialState = SpyOmsOrderItemStateQuery::create()
            ->filterByName($omsConfig->getInitialStatus())
            ->findOneOrCreate();
        $initialState->save();

        $this->assertNotNull($initialState->getIdOmsOrderItemState());

        $item1 = new ItemTransfer();
        $item1->setName('item-test-1')
            ->setSku('sku1')
            ->setUnitPrice(130)
            ->setUnitGrossPrice(120)
            ->setSumGrossPrice(120)
            ->setQuantity(1)
            ->setTaxRate(19);

        $item2 = new ItemTransfer();
        $item2->setName('item-test-2')
            ->setSku('sku2')
            ->setUnitPrice(130)
            ->setUnitGrossPrice(130)
            ->setSumGrossPrice(130)
            ->setQuantity(1)
            ->setTaxRate(19);

        $quoteTransfer->addItem($item1);
        $quoteTransfer->addItem($item2);

        $item1Query = SpySalesOrderItemQuery::create()
            ->filterByName('item-test-1');

        $item2Query = SpySalesOrderItemQuery::create()
            ->filterByName('item-test-2');

        $checkoutResponseTransfer = $this->getValidBaseResponseTransfer();
        $this->salesFacade->saveSalesOrder($quoteTransfer, $checkoutResponseTransfer->getSaveOrder());

        $savedItems = $checkoutResponseTransfer->getSaveOrder()->getOrderItems();

        $item1Entity = $item1Query->findOne();
        $item2Entity = $item2Query->findOne();

        $this->assertNotNull($item1Entity);
        $this->assertNotNull($item2Entity);

        $this->assertSame($savedItems[1]->getIdSalesOrderItem(), $item1Entity->getIdSalesOrderItem());
        $this->assertSame($item1->getName(), $item1Entity->getName());
        $this->assertSame($checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder(), $item1Entity->getFkSalesOrder());
        $this->assertSame($initialState->getIdOmsOrderItemState(), $item1Entity->getFkOmsOrderItemState());
        $this->assertSame($item1->getSku(), $item1Entity->getSku());
        $this->assertSame($savedItems[1]->getUnitGrossPrice(), $item1Entity->getGrossPrice());
        $this->assertSame(1, $item1Entity->getQuantity());

        $this->assertSame($savedItems[2]->getIdSalesOrderItem(), $item2Entity->getIdSalesOrderItem());
        $this->assertSame($item2->getName(), $item2Entity->getName());
        $this->assertSame($checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder(), $item2Entity->getFkSalesOrder());
        $this->assertSame($initialState->getIdOmsOrderItemState(), $item2Entity->getFkOmsOrderItemState());
        $this->assertSame($item2->getSku(), $item2Entity->getSku());
        $this->assertSame($savedItems[2]->getUnitGrossPrice(), $item2Entity->getGrossPrice());
        $this->assertSame(1, $item2Entity->getQuantity());
    }

    /**
     * @return void
     */
    public function testSaveOrderGeneratesOrderReference(): void
    {
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $checkoutResponseTransfer = $this->getValidBaseResponseTransfer();
        $this->salesFacade->saveSalesOrder($quoteTransfer, $checkoutResponseTransfer->getSaveOrder());
        $this->assertNotNull($checkoutResponseTransfer->getSaveOrder()->getOrderReference());
    }

    /**
     * @return void
     */
    public function testCreateSalesExpenseSavesExpense(): void
    {
        // Assign
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $saveOrderTransfer = $this->createSaveOrderTransfer();
        $this->salesFacade->saveSalesOrder($quoteTransfer, $saveOrderTransfer);
        $expenseTransfer = $this->createExpenseTransfer();
        $expenseTransfer->setFkSalesOrder($saveOrderTransfer->getIdSalesOrder());

        // Act
        $savedExpenseTransfer = $this->salesFacade->createSalesExpense($expenseTransfer);
        $expenseTransfer->setIdSalesExpense($savedExpenseTransfer->getIdSalesExpense());

        // Assert
        $this->assertNotNull($savedExpenseTransfer->getIdSalesExpense());
        $this->assertEquals($savedExpenseTransfer->toArray(), $expenseTransfer->toArray());
    }

    /**
     * @param int $expensePrice
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    protected function createExpenseTransfer(int $expensePrice = 100): ExpenseTransfer
    {
        $expenseTransfer = (new ExpenseTransfer())
            ->setName('test expense')
            ->setType('EXPENSE_TYPE')
            ->setUnitPrice($expensePrice)
            ->setSumPrice($expensePrice)
            ->setUnitPriceToPayAggregation($expensePrice)
            ->setSumPriceToPayAggregation($expensePrice)
            ->setTaxRate(19.1)
            ->setQuantity(1)
            ->setUnitGrossPrice(0)
            ->setSumGrossPrice(0)
            ->setUnitNetPrice($expensePrice)
            ->setSumNetPrice($expensePrice);

        return $expenseTransfer;
    }

    /**
     * @return \Orm\Zed\Oms\Persistence\SpyOmsOrderProcess
     */
    protected function getProcessEntity(): SpyOmsOrderProcess
    {
        $omsOrderProcessEntity = (new SpyOmsOrderProcessQuery())->filterByName('CheckoutTest01')->findOneOrCreate();
        $omsOrderProcessEntity->save();

        return $omsOrderProcessEntity;
    }

    /**
     * @return void
     */
    public function testSaveOrderCreatesOrderRawAndSavesFields(): void
    {
        //Arrange
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $saveOrderTransfer = $this->createSaveOrderTransfer();

        //Act
        $this->salesFacade->saveOrderRaw($quoteTransfer, $saveOrderTransfer);
        $orderQuery = SpySalesOrderQuery::create()
            ->filterByPrimaryKey($saveOrderTransfer->getIdSalesOrder());
        $orderEntity = $orderQuery->findOne();

        //Assert
        $this->assertNotNull($orderEntity);
        $this->assertSame('max@mustermann.de', $orderEntity->getEmail());
        $this->assertSame('Max', $orderEntity->getFirstName());
        $this->assertSame('Mustermann', $orderEntity->getLastName());
    }

    /**
     * @return void
     */
    public function testSaveOrderRawCreatesAndFillsOrderItems(): void
    {
        //Arrange
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $omsConfig = new OmsConfig();
        $quoteTransfer = $this->getValidItemsQuoteTransfer($quoteTransfer);
        $checkoutResponseTransfer = $this->getValidBaseResponseTransfer();
        $saveOrderTransfer = $checkoutResponseTransfer->getSaveOrder();

        //Act
        $initialState = SpyOmsOrderItemStateQuery::create()
            ->filterByName($omsConfig->getInitialStatus())
            ->findOneOrCreate();
        $initialState->save();

        $this->salesFacade->saveOrderRaw($quoteTransfer, $saveOrderTransfer);
        $this->salesFacade->saveSalesOrderItems($quoteTransfer, $saveOrderTransfer);

        $savedItems = $saveOrderTransfer->getOrderItems();

        $item1Entity = $this->findSalesOrderItemByName('item-test-1');
        $item2Entity = $this->findSalesOrderItemByName('item-test-2');

        //Assert
        $this->assertNotNull($initialState->getIdOmsOrderItemState());

        $this->assertNotNull($item1Entity);
        $this->assertNotNull($item2Entity);

        $this->assertSame($savedItems[1]->getIdSalesOrderItem(), $item1Entity->getIdSalesOrderItem());
        $this->assertSame($quoteTransfer->getItems()[1]->getName(), $item1Entity->getName());
        $this->assertSame($checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder(), $item1Entity->getFkSalesOrder());
        $this->assertSame($initialState->getIdOmsOrderItemState(), $item1Entity->getFkOmsOrderItemState());
        $this->assertSame($quoteTransfer->getItems()[1]->getSku(), $item1Entity->getSku());
        $this->assertSame($savedItems[1]->getUnitGrossPrice(), $item1Entity->getGrossPrice());
        $this->assertSame(1, $item1Entity->getQuantity());

        $this->assertSame($savedItems[2]->getIdSalesOrderItem(), $item2Entity->getIdSalesOrderItem());
        $this->assertSame($quoteTransfer->getItems()[2]->getName(), $item2Entity->getName());
        $this->assertSame($checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder(), $item2Entity->getFkSalesOrder());
        $this->assertSame($initialState->getIdOmsOrderItemState(), $item2Entity->getFkOmsOrderItemState());
        $this->assertSame($quoteTransfer->getItems()[2]->getSku(), $item2Entity->getSku());
        $this->assertSame($savedItems[2]->getUnitGrossPrice(), $item2Entity->getGrossPrice());
        $this->assertSame(1, $item2Entity->getQuantity());
    }

    /**
     * @return void
     */
    public function testSaveOrderCreatesOrderRawAndSavesOrderTotals(): void
    {
        //Arrange
        $quoteTransfer = $this->getValidBaseQuoteTransfer();
        $saveOrderTransfer = $this->createSaveOrderTransfer();

        //Act
        $this->salesFacade->saveOrderRaw($quoteTransfer, $saveOrderTransfer);
        $this->salesFacade->saveSalesOrderTotals($quoteTransfer, $saveOrderTransfer);

        $orderTotalsQuery = SpySalesOrderTotalsQuery::create()
            ->filterByFkSalesOrder($saveOrderTransfer->getIdSalesOrder());

        $orderTotalsEntity = $orderTotalsQuery->findOne();

        //Assert
        $this->assertNotNull($orderTotalsEntity);
        $this->assertSame(1337, $orderTotalsEntity->getGrandTotal());
        $this->assertSame(337, $orderTotalsEntity->getSubtotal());
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    private function getValidItemsQuoteTransfer(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        $shippingAddress = new AddressTransfer();
        $shippingAddress->setIso2Code('ix')
            ->setAddress1('address-1-2-test')
            ->setFirstName('Max')
            ->setLastName('Mustermann')
            ->setZipCode('1337')
            ->setCity('SpryHome');

        $shipmentTransfer = new ShipmentTransfer();
        $shipmentTransfer->setMethod(new ShipmentMethodTransfer());
        $shipmentTransfer->setShippingAddress($shippingAddress);

        $item1 = new ItemTransfer();
        $item1->setName('item-test-1')
            ->setSku('sku1')
            ->setUnitPrice(130)
            ->setUnitGrossPrice(120)
            ->setSumGrossPrice(120)
            ->setQuantity(1)
            ->setTaxRate(19)
            ->setShipment($shipmentTransfer);

        $item2 = new ItemTransfer();
        $item2->setName('item-test-2')
            ->setSku('sku2')
            ->setUnitPrice(130)
            ->setUnitGrossPrice(130)
            ->setSumGrossPrice(130)
            ->setQuantity(1)
            ->setTaxRate(19)
            ->setShipment($shipmentTransfer);

        $quoteTransfer->addItem($item1);
        $quoteTransfer->addItem($item2);

        return $quoteTransfer;
    }

    /**
     * @param string $name
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItem
     */
    private function findSalesOrderItemByName(string $name)
    {
        $itemQuery = SpySalesOrderItemQuery::create()
            ->filterByName($name);

        return $itemQuery->findOne();
    }
}
