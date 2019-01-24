<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 24/01/2019
 * Time: 1:47 PM
 */

namespace App\Form\Debts;


use App\Entity\Debts\Debts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value')
            ;
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setDefaults([
                'data_class' => Debts::class
            ]);
    }
}