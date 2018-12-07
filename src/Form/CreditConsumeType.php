<?php
/**
 * Created by PhpStorm.
 * User: JLEON
 * Date: 12/4/2018
 * Time: 5:03 PM
 */

namespace App\Form;


use App\Entity\CreditCard\CreditCardConsume;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditConsumeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('amount', MoneyType::class, array(
                'currency' => 'COP'
            ))
            ->add('dues', NumberType::class)
            ->add('interest', NumberType::class, [
                'scale' => 2
            ])
            ->add('consume_at', DateType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CreditCardConsume::class,
        ));
    }

}