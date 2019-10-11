<?php
/**
 * Created by PhpStorm.
 * User: JLEON
 * Date: 12/4/2018
 * Time: 5:03 PM
 */

namespace App\Form\Credit;


use App\Entity\CreditCard\CreditCard;
use App\Entity\CreditCard\CreditCardConsume;
use App\Entity\CreditCard\CreditCardUser;
use App\Repository\CreditCard\CreditCardRepository;
use App\Repository\CreditCard\CreditCardUserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class CreditConsumeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creditCardUser', EntityType::class, [
                'label' => 'label.credit_card.user',
                'class' => CreditCardUser::class,
                'query_builder' => function(CreditCardUserRepository $creditCardUserRepository) use ($options){
                    return $creditCardUserRepository->getByOwnerQB($options['owner']);
                },
                'choice_label' => function (CreditCardUser $owner){
                    return $owner->getAlias();
                },
                'placeholder' => '-- Select --'
            ])
            ->add('creditCard', EntityType::class, [
                'label' => 'label.credit_card.card',
                'class' => 'App\Entity\CreditCard\CreditCard',
                'query_builder' => function(CreditCardRepository $cardRepository) use ($options){
                    return $cardRepository->getByOwnerQB($options['owner']);
                },
                'choice_label' => function (CreditCard $creditCard){
                    return  $creditCard->getNumber() . ' - ' .
                            $creditCard->getOwner()->getName() . ' ' .
                            $creditCard->getOwner()->getLastName() . ' ( '.
                            $creditCard->getFranchise() . ' )';
                },
                'placeholder' => '-- Select --'
            ])
            ->add('description', TextType::class, [
                'label' => 'label.credit_consume.description'
            ])
            ->add('code', TextType::class, [
                'label' => 'label.credit_card.code'
            ])
            ->add('amount', MoneyType::class, array(
                'label' => 'label.credit_card.amount',
                'currency' => 'COP'
            ))
            ->add('dues', NumberType::class)
            ->add('interest', NumberType::class, [
                'scale' => 2
            ])
            ->add('consume_at', DateType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CreditCardConsume::class
        ));

        $resolver->setRequired('owner');
    }

}