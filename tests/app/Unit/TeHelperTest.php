<?php

namespace Tests\Unit;

use DTApi\Helpers\TeHelper;
use Carbon\Carbon;
use Tests\TestCase;

class TeHelperTest extends TestCase
{
    /**
     * Test willExpireAt method.
     *
     * @return void
     */
    public function testWillExpireAt()
    {
        $due_time = Carbon::now()->addHours(100);
        $created_at = Carbon::now();

        $result = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals($due_time->subHours(48)->format('Y-m-d H:i:s'), $result);

        $due_time = Carbon::now()->addHours(50);
        $created_at = Carbon::now();

        $result = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals($created_at->addHours(16)->format('Y-m-d H:i:s'), $result);

        $due_time = Carbon::now()->addHours(10);
        $created_at = Carbon::now();

        $result = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals($created_at->addMinutes(90)->format('Y-m-d H:i:s'), $result);

        $due_time = Carbon::now()->addMinutes(60);
        $created_at = Carbon::now();

        $result = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals($due_time->format('Y-m-d H:i:s'), $result);
    }
}