<?php

/**
 * This file is part of Antilope
 *
 * Antilope is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * PHP version 7.4
 *
 * @package Antilope
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2020-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\Repository;

use App\Entity\Sharable;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Cast\Array_;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Traversable;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param Collection|User[] $users collection of User objects
     * @return User[] Returns a collection of User objects
     */
    public function findAllExcept(Collection $users)
    {
        $ids = $users->map(function (User $user) {
            return $user->getId();
        });

        $qb = $this->createQueryBuilder('u');
        return $qb->where($qb->expr()->notIn('u.id', $ids))
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return User[]
     */
    public function findPossibleManagers(Sharable $sharable): array
    {
        $qb = $this->createQueryBuilder('uu');
        return $qb->andWhere(
            $qb->expr()->notIn('uu.id', $this->findImpossibleManagerQuery($sharable)->getDQL())
        )
            ->getQuery()
            ->getResult();
    }

    private function findImpossibleManagerQuery(Sharable $sharable): Query
    {
        $qb = $this->createQueryBuilder('u');
        return $qb->leftJoin('u.manages', 'm')
            ->leftJoin('u.interesteds', 'i')
            ->leftJoin('u.userContacts', 'uc')
            ->Where($qb->expr()->eq('m.sharable', $sharable->getId()))
            ->orWhere($qb->expr()->eq('i.sharable', $sharable->getId()))
            ->orWhere($qb->expr()->isNull('uc'))
            ->orWhere($qb->expr()->eq('u.disabled', 1))
            ->getQuery();
    }

    /**
     * @param int $minute Number of minute to filter
     * @return User[]
     */
    public function findRecentlyActive(int $minute = 15): array
    {
        $qb = $this->createQueryBuilder('u');
        return $qb->where('u.lastActivity > :lastActivity')
            ->setParameter('lastActivity', new DateTime("$minute minutes ago"), Types::DATETIME_MUTABLE)
            ->andWhere('u.disabled = 0')
            ->orderBy('u.lastActivity', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return User[] All not disabled users
     */
    public function findAllActives(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.disabled = 0')
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
