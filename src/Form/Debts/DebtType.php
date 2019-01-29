<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 24/01/2019
 * Time: 1:47 PM
 */

namespace App\Form\Debts;


use App\Entity\Debts\Creditor;
use App\Entity\Debts\Credits;
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
                'class' => 'FixedCharges',
                'query_builder' => function(EntityRepository $er) use ($options){
                    $qb =$er->createQueryBuilder('dt')
                        ->where('dt.active = true')
                        ->setParameter('user', $options['user']);
                    $qb->andWhere(
                        $qb->expr()->orX('dt.user = :user', 'dt.applyToAll = true')
                    );
                    return $qb;
                }
            ])
            ->add('value')
            ->add('dues')
            ->add('concept')
            ->add('firstPaymentDay')
            ->add('creditor', EntityType::class, [
                'class' => Creditor::class,
                'choice_label' => function(Creditor $creditor){
                    return $creditor->getOwner().' ( '.$creditor->getBank().' )';
                }
            ])
            ->add('rate')
            ;
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setDefaults([
                'data_class' => Credits::class
            ]);
    }
}