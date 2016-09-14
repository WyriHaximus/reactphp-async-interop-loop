<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class ReactTimer implements TimerInterface
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var bool
     */
    private $isPeriodic;

    /**
     * @var mixed
     */
    private $data;

    /**
     * ReactTimer constructor.
     * @param LoopInterface $loop
     * @param int $interval
     * @param callable $callback
     * @param bool $isPeriodic
     */
    public function __construct(LoopInterface $loop, $interval, callable $callback, $isPeriodic)
    {
        $this->loop = $loop;
        $this->interval = $interval;
        $this->callback = $callback;
        $this->isPeriodic = $isPeriodic;
    }

    public function getLoop()
    {
        return $this->loop;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function isPeriodic()
    {
        return $this->isPeriodic;
    }

    public function isActive()
    {
        $this->loop->isTimerActive($this);
    }

    public function cancel()
    {
        $this->loop->cancelTimer($this);
    }
}
