<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class AsyncInteropLoop implements LoopInterface
{
    private $inNextTick = false;
    private $timers = [];
    private $readStreams = [];
    private $writeStreams = [];

    public function run()
    {
        Loop::execute(
            function() {},
            Loop::get()
        );
    }

    public function stop()
    {
        Loop::stop();
    }

    public function addReadStream($stream, callable $listener)
    {
        $id = Loop::onReadable(
            $stream,
            function () use ($stream, $listener) {
                $listener($stream, $this);
            }
        );
        $this->readStreams[(int)$stream] = $id;
    }

    public function addWriteStream($stream, callable $listener)
    {
        $id = Loop::onWritable(
            $stream,
            function () use ($stream, $listener) {
                $listener($stream, $this);
            }
        );
        $this->writeStreams[(int)$stream] = $id;
    }

    public function removeReadStream($stream)
    {
        $watcherId = $this->readStreams[(int)$stream];
        unset($this->readStreams[(int)$stream]);
        Loop::cancel($watcherId);
    }

    public function removeWriteStream($stream)
    {
        $watcherId = $this->writeStreams[(int)$stream];
        unset($this->writeStreams[(int)$stream]);
        Loop::cancel($watcherId);
    }

    public function removeStream($stream)
    {
        if (isset($this->readStreams[(int)$stream])) {
            $this->removeReadStream($stream);
        }
        if (isset($this->writeStreams[(int)$stream])) {
            $this->removeWriteStream($stream);
        }
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
        if ($this->inNextTick) {
            $listener($this);
            return;
        }

        Loop::defer(function () use ($listener) {
            $previousValue = $this->inNextTick;
            $this->inNextTick = true;
            $listener($this);
            $this->inNextTick = $previousValue;
        });
    }

    public function futureTick(callable $listener)
    {
        Loop::defer(function () use ($listener) {
            $listener($this);
        });
    }

    public function tick()
    {
        Loop::defer(function () {
            Loop::stop();
        });
        $this->run();
    }
}
