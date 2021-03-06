<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use AsyncInterop\Loop;
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
        $this->readStreams[(int)$stream][] = $id;
    }

    public function addWriteStream($stream, callable $listener)
    {
        $id = Loop::onWritable(
            $stream,
            function () use ($stream, $listener) {
                $listener($stream, $this);
            }
        );
        $this->writeStreams[(int)$stream][] = $id;
    }

    public function removeReadStream($stream)
    {
        $key = (int)$stream;
        if (!isset($this->readStreams[(int)$stream])) {
            return;
        }

        $watcherIds = $this->readStreams[$key];
        unset($this->readStreams[$key]);
        foreach ($watcherIds as $watcherId) {
            Loop::cancel($watcherId);
        }
    }

    public function removeWriteStream($stream)
    {
        $key = (int)$stream;
        if (!isset($this->writeStreams[$key])) {
            return;
        }

        $watcherIds = $this->writeStreams[$key];
        unset($this->writeStreams[$key]);
        foreach ($watcherIds as $watcherId) {
            Loop::cancel($watcherId);
        }
    }

    public function removeStream($stream)
    {
        $key = (int)$stream;
        if (isset($this->readStreams[$key])) {
            $this->removeReadStream($stream);
        }
        if (isset($this->writeStreams[$key])) {
            $this->removeWriteStream($stream);
        }
    }

    private function addWrappedTimer($interval, callable $callback, $isPeriodic = false)
    {
        $wrappedCallback = function () use (&$timer, $callback) {
            $callback($timer);
        };
        $millis          = $interval * 1000;
        if ($isPeriodic) {
            $watcherId = Loop::repeat($millis, $wrappedCallback);
        } else {
            $watcherId = Loop::delay($millis, $wrappedCallback);
        }
        $timer = new ReactTimer(
            $watcherId,
            $interval,
            $callback,
            $this,
            false
        );

        $hash = spl_object_hash($timer);
        $this->timers[$hash] = $watcherId;

        return $timer;
    }

    public function addTimer($interval, callable $callback)
    {
        return $this->addWrappedTimer($interval, $callback);
    }

    /**
     * @param float|int $interval
     * @param callable $callback
     * @return ReactTimer
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        return $this->addWrappedTimer($interval, $callback, true);
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

            try {
                $listener($this);
            } finally {
                $this->inNextTick = $previousValue;
            }
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
