<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;

final class ReactTimer extends Timer
{
    private $watcherId;

    public function __construct($watcherId, $interval, callable $callback, LoopInterface $loop, $isPeriodic = false, $data = null)
    {
        parent::__construct($loop, $interval, $callback, $isPeriodic, $data);
        $this->watcherId = $watcherId;
    }

    public function cancel()
    {
        parent::cancel();
        Loop::cancel($this->watcherId);
    }
}