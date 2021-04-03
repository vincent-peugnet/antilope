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

use App\Entity\Question;
use App\Entity\QuestionSearch;
use App\Entity\User;
use App\Entity\UserClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @return Question[] Returns an array of Question objects
     */
    public function findAllVisible(User $user, QuestionSearch $search)
    {
        $userClassRepository = $this->getEntityManager()->getRepository(UserClass::class);
        assert($userClassRepository instanceof UserClassRepository);
        $visibleBy = $userClassRepository->findLowerthan($user->getUserClass());

        $visibleByIds = array_map(function (UserClass $userClass) {
            return $userClass->getId();
        }, $visibleBy);

        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.sharable', 's')
            ->leftJoin('s.managedBy', 'm')
        ;

        // Work, but there may be a better way to do this including a lot of joins
        if ($user->getUserClass()->getAccess()) {
            $qb->andwhere(
                $qb->expr()->orX(
                    $qb->expr()->in('s.visibleBy', $visibleByIds),
                    $qb->expr()->isNull('s.visibleBy'),
                    $qb->expr()->eq('m.user', $user->getId())
                )
            );
        } else {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('s.visibleBy', $visibleByIds),
                    $qb->expr()->eq('m.user', $user->getId())
                )
            );
        }

        if (!is_null($search->getSharable())) {
            $qb->andWhere(
                $qb->expr()->eq('q.sharable', $search->getSharable()->getId())
            );
        }

        if (!is_null($search->getUser())) {
            $qb->andWhere(
                $qb->expr()->eq('q.user', $search->getUser()->getId())
            );
        }

        if (!empty($search->getQuery())) {
            $qb = $qb
                ->andWhere('q.text LIKE :q')
                ->setParameter('q', "%{$search->getQuery()}%");
        }

        if ($search->getOnlyNotAnswered()) {
            $qb = $qb->leftJoin('q.answers', 'a')
                ->andWhere(
                    $qb->expr()->isNull('a.question')
                );
        }

        $qb
            ->orderBy('q.' . $search->getSortBy(), $search->getOrder())
        ;

        return $qb->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Question
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
