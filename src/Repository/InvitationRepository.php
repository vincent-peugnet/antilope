<?php

namespace App\Repository;

use App\Entity\Invitation;
use App\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    /**
     * Get all the invites that where used
     * 
    * @return Invitation[] Returns an array of Invitation objects
    */
    public function findUsedInvitations(User $user)
    {
        $qb = $this->createQueryBuilder('i');
        $result = $qb->andWhere($qb->expr()->isNotNull("i.child"))
            ->andWhere('i.parent = :parent')
            ->setParameter('parent', $user->getId())
            ->getQuery()
            ->getResult();
        return $result;
    }

    /**
     * Get the current actives invites of an user
     * @return Invitation[] 
     */
    public function findActiveInvitations(User $user, DateInterval $invitationDuration)
    {
        $passed = new DateTime();
        $passed->sub($invitationDuration);

        $qb = $this->createQueryBuilder('i');
        $result = $qb->andWhere($qb->expr()->isNull("i.child"))
            ->andWhere('i.parent = :parent')
            ->andWhere('i.createdAt > :passed')
            ->setParameter('parent', $user->getId())
            ->setParameter('passed', $passed)
            ->getQuery()
            ->getResult();
        return $result;
    }


    /*
    public function findOneBySomeField($value): ?Invitation
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
