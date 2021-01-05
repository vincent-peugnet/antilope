<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserClass;
use App\Repository\UserClassRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class LevelUp
{
    private $userClassRepository;
    private $em;

    public function __construct(UserClassRepository $userClassRepository, EntityManagerInterface $em) {
        $this->userClassRepository = $userClassRepository;
        $this->em = $em;
    }

    /**
     * Call the Check method and persist User in case of promotion
     */
    public function checkUpdate(User $user): User
    {
        $originalUserClass = $user->getUserClass();
        $user = $this->check($user);
        if ($user->getUserClass() !== $originalUserClass) {
            // User is promoted !
            $this->em->persist($user);
            $this->em->flush();
        }
        return $user;
    }

    /**
     * Check if the User can level Up and update It
     * 
     * @param User $user
     * @return User the updated or not user
     * @todo find a way to indicate that the user as been promoted
     */
    public function check(User $user): User
    {
        $userClass = $this->userClassRepository->findNext($user->getUserClass());
        if ($userClass) {
            if (
                $this->shareScore($user, $userClass) &&
                $this->accountAge($user, $userClass) &&
                $this->validated($user, $userClass) &&
                $this->verified($user, $userClass)
            ) {
                $user->setUserClass($userClass);
                $this->check($user);
            }
        }
        return $user;
    }

    private function shareScore(User $user, UserClass $userClass)
    {
        return ($user->getShareScore()>= $userClass->getShareScoreReq());
    }

    private function accountAge(User $user, UserClass $userClass)
    {
        $interval = new DateInterval('P' .$userClass->getAccountAgeReq(). 'D');
        $ageReq = $user->getCreatedAt()->add($interval);
        return ($ageReq < new DateTime());
    }

    private function validated(User $user, UserClass $userClass)
    {
        return (count($user->getValidations()) >= $userClass->getValidatedReq());
    }

    private function verified(User $user, UserClass $userClass)
    {
        return ($userClass->getVerifiedReq() && $user->isVerified() || !$userClass->getVerifiedReq());
    }
}