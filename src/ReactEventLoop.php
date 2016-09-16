<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop\Driver;
use Interop\Async\Loop\InvalidWatcherException;
use Interop\Async\Loop\UnsupportedFeatureException;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

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
    private $watchers = [];

    /**
     * @var array
     */
    private $activeWatchers = [];

    /**
     * @var array
     */
    private $defers = [];

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
        $this->watchers[$watcherId] = $watcherId;

        if (count($this->defers) === 0) {
            $this->setDeferFutureTick();
        }

        $this->defers[$watcherId] = [
            'callback' => $callback,
            'data' => $data,
        ];

        return $watcherId;
    }

    protected function setDeferFutureTick()
    {
        $this->loop->futureTick(function () {
            foreach ($this->defers as $watcherId => $defered) {
                if (!isset($this->activeWatchers[$watcherId])) {
                    continue;
                }

                $callback = $defered['callback'];
                $data = $defered['data'];

                $callback($data);

                unset(
                    $this->activeWatchers[$watcherId],
                    $this->watchers[$watcherId],
                    $this->defers[$watcherId]
                );
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delay($delay, callable $callback, $data = null)
    {
        $watcherId = $this->nextId++;

        $this->activeWatchers[$watcherId] = $this->loop->addTimer($delay / 1000, function () use ($callback, $data, $watcherId) {
            if (isset($this->activeWatchers)) {
                $callback($data);
            }

            unset(
                $this->activeWatchers[$watcherId],
                $this->watchers[$watcherId]
            );
        });

        return $watcherId;
    }

    /**
     * {@inheritdoc}
     */
    public function repeat($interval, callable $callback, $data = null)
    {
        $watcherId = $this->nextId++;

        $this->activeWatchers[$watcherId] = $this->loop->addPeriodicTimer($interval / 1000, function (TimerInterface $timer) use ($callback, $data, $watcherId) {
            if (isset($this->activeWatchers[$watcherId])) {
                $callback($data);
            }

            if (isset($this->watchers[$watcherId])) {

            }

            $timer->cancel();

            unset(
                $this->activeWatchers[$watcherId],
                $this->watchers[$watcherId]
            );
        });

        return $watcherId;
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
        if (!isset($this->watchers[$watcherId])) {
            throw new InvalidWatcherException();
        }

        $this->activeWatchers[$watcherId] = $watcherId;

        if (key_exists($watcherId, $this->defers)) {
            $this->setDeferFutureTick();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disable($watcherId)
    {
        unset($this->activeWatchers[$watcherId]);
    }

    /**
     * {@inheritdoc}
     */
    public function cancel($watcherId)
    {
        unset($this->activeWatchers[$watcherId]);

        if (key_exists($watcherId, $this->defers)) {
            unset($this->defers[$watcherId]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reference($watcherId)
    {
        if (!isset($this->watchers[$watcherId])) {
            throw new InvalidWatcherException();
        }

        $this->activeWatchers[$watcherId] = $watcherId;
        $this->watchers[$watcherId] = $watcherId;

        if (in_array($watcherId, $this->defers)) {
            $this->setDeferFutureTick();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unreference($watcherId)
    {
        unset(
            $this->activeWatchers[$watcherId],
            $this->watchers[$watcherId]
        );
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
