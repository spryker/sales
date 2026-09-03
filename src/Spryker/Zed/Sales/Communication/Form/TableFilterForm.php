<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Communication\Form;

use DateTime;
use Generated\Shared\Transfer\OrderTableCriteriaTransfer;
use Spryker\Zed\Gui\Communication\Form\Type\DateTimePickerType;
use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Spryker\Zed\Sales\Communication\Form\DataProvider\TableFilterFormDataProvider;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \Spryker\Zed\Sales\Business\SalesFacadeInterface getFacade()
 * @method \Spryker\Zed\Sales\Communication\SalesCommunicationFactory getFactory()
 * @method \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Sales\SalesConfig getConfig()
 * @method \Spryker\Zed\Sales\Persistence\SalesRepositoryInterface getRepository()
 */
class TableFilterForm extends AbstractType
{
    /**
     * @var string
     */
    protected const FIELD_STATUSES = 'statuses';

    /**
     * @var string
     */
    protected const FIELD_STORES = 'stores';

    /**
     * @var string
     */
    protected const FIELD_ORDER_DATE_FROM = 'order_date_from';

    /**
     * @var string
     */
    protected const FIELD_ORDER_DATE_TO = 'order_date_to';

    /**
     * @var string
     */
    protected const PLACEHOLDER_STATUSES = 'Select Statuses';

    /**
     * @var string
     */
    protected const PLACEHOLDER_STORES = 'Select Stores';

    /**
     * @var string
     */
    protected const LABEL_STORE = 'Store';

    /**
     * @var string
     */
    protected const LABEL_STATUS = 'Status';

    /**
     * @var string
     */
    protected const LABEL_ORDER_DATE_FROM = 'Order date from';

    /**
     * @var string
     */
    protected const LABEL_ORDER_DATE_TO = 'Order date to';

    /**
     * @var string
     */
    protected const DATE_TIME_FORMAT = 'Y-m-d\TH:i';

    /**
     * @var string
     */
    protected const RANGE_ROLE_START = 'start';

    /**
     * @var string
     */
    protected const RANGE_ROLE_END = 'end';

    /**
     * @var string
     */
    protected const RANGE_GROUP_ORDER_DATE = 'sales-order-date';

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            TableFilterFormDataProvider::OPTION_STATUSES,
            TableFilterFormDataProvider::OPTION_STORES,
            TableFilterFormDataProvider::OPTION_CURRENT_TIMEZONE,
        ]);

        $resolver->setDefaults([
            'data_class' => OrderTableCriteriaTransfer::class,
            'csrf_protection' => false,
        ]);

        foreach ($this->getFactory()->getOrdersTableFilterFormExpanderPlugins() as $ordersTableFilterFormExpanderPlugin) {
            $ordersTableFilterFormExpanderPlugin->expandConfigureOptions($resolver);
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(Request::METHOD_GET);

        $this
            ->addStatusField($builder, $options)
            ->addStoreField($builder, $options)
            ->addOrderDateFromField($builder, $options)
            ->addOrderDateToField($builder, $options);

        foreach ($this->getFactory()->getOrdersTableFilterFormExpanderPlugins() as $ordersTableFilterFormExpanderPlugin) {
            $ordersTableFilterFormExpanderPlugin->expandForm($builder, $options);
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addStatusField(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add(static::FIELD_STATUSES, ChoiceType::class, [
            'label' => static::LABEL_STATUS,
            'placeholder' => static::PLACEHOLDER_STATUSES,
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'choices' => $options[TableFilterFormDataProvider::OPTION_STATUSES] ?? [],
            'attr' => [
                'class' => 'spryker-form-select2combobox',
                'data-placeholder' => $this->getFactory()->getTranslatorFacade()->trans(static::PLACEHOLDER_STATUSES),
                'data-clearable' => true,
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addStoreField(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add(static::FIELD_STORES, ChoiceType::class, [
            'label' => static::LABEL_STORE,
            'placeholder' => static::PLACEHOLDER_STORES,
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'choices' => $options[TableFilterFormDataProvider::OPTION_STORES] ?? [],
            'attr' => [
                'class' => 'spryker-form-select2combobox',
                'data-placeholder' => $this->getFactory()->getTranslatorFacade()->trans(static::PLACEHOLDER_STORES),
                'data-clearable' => true,
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addOrderDateFromField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            static::FIELD_ORDER_DATE_FROM,
            $this->getDateTimeFieldType(),
            $this->getDateTimeFieldOptions(
                static::LABEL_ORDER_DATE_FROM,
                static::RANGE_ROLE_START,
                $options[TableFilterFormDataProvider::OPTION_CURRENT_TIMEZONE],
            ),
        );

        $builder->get(static::FIELD_ORDER_DATE_FROM)
            ->addModelTransformer($this->createDateTimeTransformer());

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addOrderDateToField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            static::FIELD_ORDER_DATE_TO,
            $this->getDateTimeFieldType(),
            $this->getDateTimeFieldOptions(
                static::LABEL_ORDER_DATE_TO,
                static::RANGE_ROLE_END,
                $options[TableFilterFormDataProvider::OPTION_CURRENT_TIMEZONE],
            ),
        );

        $builder->get(static::FIELD_ORDER_DATE_TO)
            ->addModelTransformer($this->createDateTimeTransformer());

        return $this;
    }

    protected function getDateTimeFieldType(): string
    {
        if ($this->isGuiDateTimePickerTypeAvailable()) {
            return DateTimePickerType::class;
        }

        return DateTimeType::class;
    }

    /**
     * @param string $label
     * @param string $rangeRole
     * @param string $viewTimezone
     *
     * @return array<string, mixed>
     */
    protected function getDateTimeFieldOptions(string $label, string $rangeRole, string $viewTimezone): array
    {
        $options = [
            'label' => $label,
            'required' => false,
            'view_timezone' => $viewTimezone,
        ];

        // The picker keeps both ends of the range consistent, which the native control cannot do.
        if ($this->isGuiDateTimePickerTypeAvailable()) {
            return $options + [
                'range_group' => static::RANGE_GROUP_ORDER_DATE,
                'range_role' => $rangeRole,
            ];
        }

        return $options + [
            'widget' => 'single_text',
            'html5' => true,
        ];
    }

    protected function isGuiDateTimePickerTypeAvailable(): bool
    {
        return class_exists(DateTimePickerType::class);
    }

    /**
     * @return \Symfony\Component\Form\DataTransformerInterface<string|\DateTimeInterface|null, \DateTimeInterface|null>
     */
    protected function createDateTimeTransformer(): DataTransformerInterface
    {
        return new CallbackTransformer(
            function ($dateAsString) {
                if (!$dateAsString) {
                    return null;
                }

                if ($dateAsString instanceof DateTime) {
                    return $dateAsString;
                }

                return new DateTime($dateAsString);
            },
            function ($dateAsObject) {
                if (!$dateAsObject) {
                    return null;
                }

                if ($dateAsObject instanceof DateTime) {
                    return $dateAsObject->format(static::DATE_TIME_FORMAT);
                }

                return $dateAsObject;
            },
        );
    }
}
