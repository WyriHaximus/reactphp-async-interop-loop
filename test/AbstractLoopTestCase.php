<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use React\EventLoop\Timer\TimerInterface;
use React\Tests\EventLoop\AbstractLoopTest;


abstract class AbstractLoopTestCase extends AbstractLoopTest
{
    public function testAddTimerShouldPassTimerToCallback()
    {
        $called      = false;
        $returnTimer = $this->loop->addTimer(1, function (TimerInterface $timer) use (&$called, &$returnTimer) {
            $called = true;
            $this->assertSame($timer, $returnTimer);

        });

        $this->loop->run();

        $this->assertTrue($called);
    }

    public function testAddPeriodicTimerShouldPassTimerToCallback()
    {
        $called = false;
        $count  = 0;

        $returnTimer = $this->loop->addPeriodicTimer(1, function (TimerInterface $timer) use (&$called, &$returnTimer, &$count) {
            $called = true;
            $this->assertSame($timer, $returnTimer);
            $timer->cancel();
            $count++;
        });

        $this->loop->run();

        $this->assertTrue($called);
        $this->assertEquals(1, $count);
    }
}