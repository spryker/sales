<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Sales\Communication\Form;

use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Spryker\Zed\Sales\SalesConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @method \Spryker\Zed\Sales\Business\SalesFacadeInterface getFacade()
 * @method \Spryker\Zed\Sales\Communication\SalesCommunicationFactory getFactory()
 * @method \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Sales\SalesConfig getConfig()
 * @method \Spryker\Zed\Sales\Persistence\SalesRepositoryInterface getRepository()
 */
class AddressForm extends AbstractType
{
    /**
     * @var string
     */
    public const FIELD_SALUTATION = 'salutation';

    /**
     * @var string
     */
    public const FIELD_FIRST_NAME = 'first_name';

    /**
     * @var string
     */
    public const FIELD_MIDDLE_NAME = 'middle_name';

    /**
     * @var string
     */
    public const FIELD_LAST_NAME = 'last_name';

    /**
     * @var string
     */
    public const FIELD_EMAIL = 'email';

    /**
     * @var string
     */
    public const FIELD_ADDRESS_1 = 'address1';

    /**
     * @var string
     */
    public const FIELD_ADDRESS_2 = 'address2';

    /**
     * @var string
     */
    public const FIELD_COMPANY = 'company';

    /**
     * @var string
     */
    public const FIELD_CITY = 'city';

    /**
     * @var string
     */
    public const FIELD_ZIP_CODE = 'zip_code';

    /**
     * @var string
     */
    public const FIELD_PO_BOX = 'po_box';

    /**
     * @var string
     */
    public const FIELD_PHONE = 'phone';

    /**
     * @var string
     */
    public const FIELD_CELL_PHONE = 'cell_phone';

    /**
     * @var string
     */
    public const FIELD_DESCRIPTION = 'description';

    /**
     * @var string
     */
    public const FIELD_COMMENT = 'comment';

    /**
     * @var string
     */
    public const FIELD_FK_COUNTRY = 'fkCountry';

    /**
     * @var string
     */
    public const OPTION_SALUTATION_CHOICES = 'salutation_choices';

    /**
     * @var string
     */
    public const OPTION_COUNTRY_CHOICES = 'country';

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'address';
    }

    /**
     * @deprecated Use {@link getBlockPrefix()} instead.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(static::OPTION_SALUTATION_CHOICES);
        $resolver->setRequired(static::OPTION_COUNTRY_CHOICES);
    }

    /**
     * @deprecated Use {@link configureOptions()} instead.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addSalutationField($builder, $options[static::OPTION_SALUTATION_CHOICES])
            ->addFirstNameField($builder)
            ->addMiddleNameField($builder)
            ->addLastNameField($builder)
            ->addEmailField($builder)
            ->addCountryField($builder, $options[static::OPTION_COUNTRY_CHOICES])
            ->addAddress1Field($builder)
            ->addAddress2Field($builder)
            ->addCompanyField($builder)
            ->addCityField($builder)
            ->addZipCodeField($builder)
            ->addPoBoxField($builder)
            ->addPhoneField($builder)
            ->addCellPhoneField($builder)
            ->addDescriptionField($builder)
            ->addCommentField($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $choices
     *
     * @return $this
     */
    protected function addSalutationField(FormBuilderInterface $builder, array $choices)
    {
        $builder->add(static::FIELD_SALUTATION, ChoiceType::class, [
            'label' => 'Salutation',
            'placeholder' => '-select-',
            'choices' => array_flip($choices),
            'required' => false,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addFirstNameField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_FIRST_NAME, TextType::class, [
            'label' => 'First name',
            'constraints' => [
                $this->createNotBlankConstraint(),
                $this->createFirstNameRegexConstraint(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addMiddleNameField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_MIDDLE_NAME, TextType::class, [
            'required' => false,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addLastNameField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_LAST_NAME, TextType::class, [
            'label' => 'Last name',
            'constraints' => [
                $this->createNotBlankConstraint(),
                $this->createLastNameRegexConstraint(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addEmailField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_EMAIL, TextType::class, [
            'constraints' => [
                new Email(),
            ],
            'required' => false,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $choices
     *
     * @return $this
     */
    protected function addCountryField(FormBuilderInterface $builder, array $choices)
    {
        $builder->add(static::FIELD_FK_COUNTRY, ChoiceType::class, [
            'label' => 'Country',
            'placeholder' => '-select-',
            'choices' => array_flip($choices),
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addAddress1Field(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_ADDRESS_1, TextType::class, [
            'label' => 'Address1',
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addAddress2Field(FormBuilderInterface $builder)
    {
        $builder->add(
            static::FIELD_ADDRESS_2,
            TextType::class,
            [
                'required' => false,
            ],
        );

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCompanyField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_COMPANY, TextType::class, ['required' => false]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCityField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_CITY, TextType::class, [
            'label' => 'City',
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addZipCodeField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_ZIP_CODE, TextType::class, [
            'label' => 'Zip code',
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addPoBoxField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_PO_BOX, TextType::class, ['required' => false]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addPhoneField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_PHONE, TextType::class, ['required' => false]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCellPhoneField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_CELL_PHONE, TextType::class, ['required' => false]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addDescriptionField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_DESCRIPTION, TextType::class, ['required' => false]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCommentField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_COMMENT, TextareaType::class, ['required' => false]);

        return $this;
    }

    /**
     * @return \Symfony\Component\Validator\Constraints\NotBlank
     */
    protected function createNotBlankConstraint(): NotBlank
    {
        return new NotBlank();
    }

    /**
     * @return \Symfony\Component\Validator\Constraints\Regex
     */
    protected function createFirstNameRegexConstraint(): Regex
    {
        return new Regex([
            'pattern' => SalesConfig::PATTERN_FIRST_NAME,
        ]);
    }

    /**
     * @return \Symfony\Component\Validator\Constraints\Regex
     */
    protected function createLastNameRegexConstraint(): Regex
    {
        return new Regex([
            'pattern' => SalesConfig::PATTERN_LAST_NAME,
        ]);
    }
}
