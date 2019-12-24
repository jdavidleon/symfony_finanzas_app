<?php


namespace App\Form\Credit;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('card', HiddenType::class, [
                'data' => $options['card']->getId(),
                'required' => true,
            ])
            ->add('card_user', HiddenType::class, [
                'data' => $options['card_user']->getId(),
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'card' => null,
                'card_user' => null,
            ]);
    }
}