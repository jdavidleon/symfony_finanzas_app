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
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('user', EntityType::class, [
                'class' => 'App\Entity\CreditCard\CreditCardUser',
                'choice_label' => 'alias',
                'query_builder' => function (EntityRepository $er) use ( $options ){
                    return $er->createQueryBuilder('ccu')
                        ->where('ccu.parent = :user')
                        ->andWhere('ccu.deleted_at IS NULL')
                        ->setParameter('user', $options['user'] );
                }
            ])
            ->add('creditCard', EntityType::class, [
                'class' => 'App\Entity\CreditCard\CreditCard',
                'choice_label' => function (CreditCard $creditCard){
                    return  $creditCard->getNumber() . ' - ' .
                            $creditCard->getOwner()->getName() . ' ' .
                            $creditCard->getOwner()->getLastName() . ' ( '.
                            $creditCard->getFranchise() . ' )';
                }
            ])
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
            'user' => null
        ));
    }

}