<?php

namespace CF\WordPress\Constants;

class Plans
{
    const FREE_PLAN = 'free';
    const PRO_PLAN = 'pro';
    const BIZ_PLAN = 'business';
    const ENT_PLAN = 'enterprise';

    public static function planNeedsUpgrade($currentPlan, $minimumPlan)
    {
        $planList = array();
        $planList[self::FREE_PLAN] = 0;
        $planList[self::PRO_PLAN] = 1;
        $planList[self::BIZ_PLAN] = 2;
        $planList[self::ENT_PLAN] = 3;

        return $planList[$currentPlan] < $planList[$minimumPlan];
    }
}
