<?php

namespace App\Service;

use App\Entity\Sharable;
use App\Entity\User;

class SharePoints
{
    protected $user;
    protected $sharable;

    public function __construct(User $user, Sharable $sharable) {
        $this->user = $user;
        $this->sharable = $sharable;
    }

    public function calculate()
    {
        $userRank = $this->user->getUserClass()->getRank();
        $validations = $this->sharable->getValidations();
        $validationCount = count($validations);
        $managers = $this->sharable->getManagedBy();
        $managerCount = count($managers);

        $rankPoint = 100 * log10($userRank + 11) - 100;
        $validationRatio = ( 4 / ( $validationCount + 0.5 ));
        $points = round($rankPoint * $validationRatio / $managerCount);

        dump("rankPoint $rankPoint");
        dump("validationRatio $validationRatio");
        dump("managerCount $managerCount");

        return $points;
    }



}