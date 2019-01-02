<?php

namespace App\Form\Credit;

use App\Entity\CreditCard\CreditCardUser;
use Doctrine\ORM\Mapping\Entity;
use function Sodium\add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('alias')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreditCardUser::class,
        ]);
    }
}
