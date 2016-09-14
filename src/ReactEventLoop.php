<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop\Driver;
use Interop\Async\Loop\InvalidWatcherException;
use Interop\Async\Loop\UnsupportedFeatureException;
use React\EventLoop\LoopInterface;

class ReactEventLoop extends Driver
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var string
     */
    private $nextId = 'a';

    /**
     * @var array
     */
    private $activeWatchers = [];

    /**
     * ReactEventLoop constructor.
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->loop->run();
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        $this->loop->stop();
    }

    /**
     * {@inheritdoc}
     */
    public function defer(callable $callback, $data = null)
    {
        $watcherId = $this->nextId++;
        $this->activeWatchers[$watcherId] = $watcherId;

        $this->loop->futureTick(function () use ($callback, $data, $watcherId) {
            if (!isset($this->activeWatchers)) {
                return;
            }

            $callback($data);

            unset($this->activeWatchers[$watcherId]);
        });

        return $watcherId;
    }

    /**
     * {@inheritdoc}
     */
    public function delay($delay, callable $callback, $data = null)
    {
        $watcherId = $this->nextId++;

        $this->activeWatchers[$watcherId] = $this->loop->addTimer($delay, function () use ($callback, $data, $watcherId) {
            if (!isset($this->activeWatchers)) {
                return;
            }

            $callback($data);

            unset($this->activeWatchers[$watcherId]);
        });

        return $watcherId;
    }

    /**
     * {@inheritdoc}
     */
    public function repeat($interval, callable $callback, $data = null)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function onReadable($stream, callable $callback, $data = null)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function onWritable($stream, callable $callback, $data = null)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function onSignal($signo, callable $callback, $data = null)
    {
        throw new UnsupportedFeatureException();
    }

    /**
     * {@inheritdoc}
     */
    public function enable($watcherId)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function disable($watcherId)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function cancel($watcherId)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function reference($watcherId)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function unreference($watcherId)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorHandler(callable $callback = null)
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function getHandle()
    {
        throw new \Exception();
    }

}
