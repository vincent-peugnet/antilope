<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserClass;
use App\Repository\UserClassRepository;
use DateInterval;
use DateTime;

class LevelUp
{
    private $userClassRepository;

    public function __construct(UserClassRepository $userClassRepository) {
        $this->userClassRepository = $userClassRepository;
    }

    /**
     * Check if the User can level Up and update It
     * 
     * @param User $user
     * @return User the updated or not user
     * @todo find a way to indicate that the user as been promoted
     */
    public function check(User $user, $offsets = ['shareScore' => 0, 'accountAge' => 0, 'validated' => 0]): User
    {
        $shareScoreOffset = $offsets['shareScore'] ?? 0;
        $accountAgeOffset = $offsets['accountAge'] ?? 0;
        $validatedOffset = $offsets['validated'] ?? 0;

        $userClass = $this->userClassRepository->findNext($user->getUserClass());
        if ($userClass) {
            if (
                $this->shareScore($user, $userClass, $shareScoreOffset) &&
                $this->accountAge($user, $userClass, $accountAgeOffset) &&
                $this->validated($user, $userClass, $validatedOffset)
            ) {
                $user->setUserClass($userClass);
            }
        }
        return $user;
    }

    private function shareScore(User $user, UserClass $userClass, int $offset = 0)
    {
        return ($user->getShareScore() +$offset >= $userClass->getShareScoreReq());
    }

    private function accountAge(User $user, UserClass $userClass, int $offset = 0)
    {
        $accountAgeReq = $userClass->getAccountAgeReq() + $offset;
        $interval = new DateInterval('P' .$accountAgeReq. 'D');
        $ageReq = $user->getCreatedAt()->add($interval);
        return ($ageReq < new DateTime());
    }

    private function validated(User $user, UserClass $userClass, int $offset = 0)
    {
        return (count($user->getValidations()) + $offset >= $userClass->getValidatedReq());
    }
}