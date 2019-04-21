<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 20/12/2018
 * Time: 12:00 PM
 */

namespace App\Form\Credit;


use App\Entity\CreditCard\CreditCard;
use App\Entity\Security\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number')
            ->add('franchise')
            ->add('description');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
           'data_class' => CreditCard::class
        ));
    }
}