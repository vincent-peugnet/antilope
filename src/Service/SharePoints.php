<?php

namespace App\Service;

use App\Entity\Sharable;
use App\Entity\User;

class SharePoints
{
    public function calculate(User $user, Sharable $sharable)
    {
        $userRank = $user->getUserClass()->getRank();
        $validations = $sharable->getValidations();
        $validationCount = count($validations);
        $managedBy = $sharable->getManagedBy();
        $managerCount = count($managedBy);

        $rankPoint = 100 * log10($userRank + 11) - 100;
        $validationRatio = ( 4 / ( $validationCount + 1 ));
        $points = round($rankPoint * $validationRatio / $managerCount);

        return $points;
    }



}