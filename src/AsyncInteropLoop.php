<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class AsyncInteropLoop implements LoopInterface
{
    private $timers = [];

    public function run()
    {
        Loop::execute(function() {});
    }

    public function stop()
    {
        Loop::stop();
    }

    public function addReadStream($stream, callable $listener)
    {
        // TODO: Implement addReadStream() method.
    }

    public function addWriteStream($stream, callable $listener)
    {
        // TODO: Implement addWriteStream() method.
    }

    public function removeReadStream($stream)
    {
        // TODO: Implement removeReadStream() method.
    }

    public function removeWriteStream($stream)
    {
        // TODO: Implement removeWriteStream() method.
    }

    public function removeStream($stream)
    {
        // TODO: Implement removeStream() method.
    }

    public function addTimer($interval, callable $callback)
    {
        $time = $interval / 1000;
        $watcherId = Loop::delay($time, $callback);

        $timer = new ReactTimer($this, $interval, $callback, false);

        $hash = spl_object_hash($timer);
        $this->timers[$hash] = $watcherId;

        return $timer;
    }

    public function addPeriodicTimer($interval, callable $callback)
    {
        $time = $interval / 1000;
        $watcherId = Loop::repeat($time, $callback);

        $timer = new ReactTimer($this, $interval, $callback, true);

        $hash = spl_object_hash($timer);
        $this->timers[$hash] = $watcherId;

        return $timer;
    }

    public function cancelTimer(TimerInterface $timer)
    {
        $hash = spl_object_hash($timer);
        if (!isset($this->timers[$hash])) {
            return;
        }

        Loop::disable($this->timers[$hash]);
        unset($this->timers[$hash]);
    }

    public function isTimerActive(TimerInterface $timer)
    {
        $hash = spl_object_hash($timer);
        return isset($this->timers[$hash]);
    }

    public function nextTick(callable $listener)
    {
        $this->futureTick($listener);
    }

    public function futureTick(callable $listener)
    {
        Loop::defer(function () use ($listener) {
            $listener($this);
        });
    }

    public function tick()
    {
        Loop::execute(
            function() {
                Loop::defer(function () {
                    Loop::stop();
                });
            },
            Loop::get()
        );
    }
}
