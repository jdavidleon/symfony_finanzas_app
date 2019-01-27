<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 24/01/2019
 * Time: 1:47 PM
 */

namespace App\Form\Debts;


use App\Entity\Debts\Debt;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debtType', EntityType::class, [
                'class' => 'App\Entity\Debts\DebtsTypes',
                'query_builder' => function(EntityRepository $er){
                    $qb =$er->createQueryBuilder('dt')
                        ->where('dt.active = true')
                        ->andWhere('dt.user = :user');

                    $qb->andWhere(
                        $qb->expr()->orX('dt.user = :user', 'dt.applyToAll = true')
                    );
                    return $qb;
                }
            ])
            ->add('value')
            ->add('dues')
            ->add('concepto')
            ->add('firstPaymentDay')
            ->add('creditor')
            ->add('tasa')
            ;
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setDefaults([
                'data_class' => Debt::class
            ]);
    }
}