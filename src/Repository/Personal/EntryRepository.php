<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 24/01/2019
 * Time: 4:53 PM
 */

namespace App\Repository\Personal;


use App\Entity\Personal\PersonalBalance;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class EntryRepository extends ServiceEntityRepository
{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PersonalBalance::class);
    }

    public function getEntriesByUser(User $user, $limit)
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->setParameters([
                'user' => $user
            ])
            ->setMaxResults($limit)
            ->orderBy('e.createdAt DESC')
            ->getQuery()
            ->getResult();
    }

}