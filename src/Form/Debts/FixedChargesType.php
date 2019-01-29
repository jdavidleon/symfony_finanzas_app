<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 29/01/2019
 * Time: 10:49 AM
 */

namespace App\Form\Debts;


use App\Entity\Debts\FixedCharges;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FixedChargesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('concept')
            ->add('payDay')
            ->add('value', MoneyType::class, [
                'currency' => 'COP'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => FixedCharges::class
            ]);
    }

}

