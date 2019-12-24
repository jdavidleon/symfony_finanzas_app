<?php

namespace App\Form\Credit;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'total to pay',
                'currency' => 'COP',
                'data' => $options['total_to_pay'],
                'invalid_message' => "label.error.invalid_money_format",
                'required' => true,
                'scale' => 0,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
               'total_to_pay' => 0
            ]);
    }
}