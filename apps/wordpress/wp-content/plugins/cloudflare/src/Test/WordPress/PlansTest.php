<?php

namespace CF\Test\WordPress;

use CF\WordPress\Constants\Plans;

class PlansTest extends \PHPUnit\Framework\TestCase
{
    public function setup(): void
    {
    }

    public function testPlanNeedsUpgradeAllCases()
    {
        // FREE_PLAN
        $this->assertFalse(Plans::planNeedsUpgrade(Plans::FREE_PLAN, Plans::FREE_PLAN));
        $this->assertTrue(Plans::planNeedsUpgrade(Plans::FREE_PLAN, Plans::PRO_PLAN));
        $this->assertTrue(Plans::planNeedsUpgrade(Plans::FREE_PLAN, Plans::BIZ_PLAN));
        $this->assertTrue(Plans::planNeedsUpgrade(Plans::FREE_PLAN, Plans::ENT_PLAN));

        $this->assertFalse(Plans::planNeedsUpgrade(Plans::PRO_PLAN, Plans::FREE_PLAN));
        $this->assertFalse(Plans::planNeedsUpgrade(Plans::PRO_PLAN, Plans::PRO_PLAN));
        $this->assertTrue(Plans::planNeedsUpgrade(Plans::PRO_PLAN, Plans::BIZ_PLAN));
        $this->assertTrue(Plans::planNeedsUpgrade(Plans::PRO_PLAN, Plans::ENT_PLAN));

        $this->assertFalse(Plans::planNeedsUpgrade(Plans::BIZ_PLAN, Plans::FREE_PLAN));
        $this->assertFalse(Plans::planNeedsUpgrade(Plans::BIZ_PLAN, Plans::PRO_PLAN));
        $this->assertFalse(Plans::planNeedsUpgrade(Plans::BIZ_PLAN, Plans::BIZ_PLAN));
        $this->assertTrue(Plans::planNeedsUpgrade(Plans::BIZ_PLAN, Plans::ENT_PLAN));

        $this->assertFalse(Plans::planNeedsUpgrade(Plans::ENT_PLAN, Plans::FREE_PLAN));
        $this->assertFalse(Plans::planNeedsUpgrade(Plans::ENT_PLAN, Plans::PRO_PLAN));
        $this->assertFalse(Plans::planNeedsUpgrade(Plans::ENT_PLAN, Plans::BIZ_PLAN));
        $this->assertFalse(Plans::planNeedsUpgrade(Plans::ENT_PLAN, Plans::ENT_PLAN));
    }
}
