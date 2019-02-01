<?php
/**
 * Created by PhpStorm.
 * User: IT OPTIME
 * Date: 1/02/2019
 * Time: 3:13 PM
 */

namespace App\Repository\Personal;


use App\Entity\Personal\Egress;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class EgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Egress::class);
    }

    public function getEgressesByUser(User $user, $limit)
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