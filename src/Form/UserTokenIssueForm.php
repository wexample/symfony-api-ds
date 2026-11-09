<?php

namespace Wexample\SymfonyApiDs\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Wexample\SymfonyForms\Form\AbstractForm;
use Wexample\SymfonyForms\Form\Type\DateInputType;
use Wexample\SymfonyForms\Form\Type\TextInputType;

/**
 * A token issued to the signed-in account: what it is for, and the day it
 * stops working if it should — none by default.
 */
class UserTokenIssueForm extends AbstractForm
{
    public const string FIELD_LABEL = 'label';

    public const string FIELD_EXPIRATION = 'expiration';

    public static bool $ajax = true;

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add(self::FIELD_LABEL, TextInputType::class, [
                self::FIELD_OPTION_NAME_LABEL => true,
                self::FIELD_OPTION_NAME_REQUIRED => true,
                self::FIELD_OPTION_NAME_MAPPED => false,
            ])
            ->add(self::FIELD_EXPIRATION, DateInputType::class, [
                self::FIELD_OPTION_NAME_LABEL => true,
                self::FIELD_OPTION_NAME_REQUIRED => false,
                self::FIELD_OPTION_NAME_MAPPED => false,
                'input' => 'datetime_immutable',
            ]);
        $this->builderAddSubmit($builder);
    }
}
