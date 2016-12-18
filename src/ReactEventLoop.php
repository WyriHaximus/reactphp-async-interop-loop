<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop\Driver;
use Interop\Async\Loop\InvalidWatcherException;
use Interop\Async\Loop\UnsupportedFeatureException;
use InvalidArgumentException;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

final class ReactEventLoop extends Driver
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var bool
     */
    private $running = false;

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
    private $defers = [];

    /**
     * @var array
     */
    private $delayed = [];

    /**
     * @var array
     */
    private $repeats = [];

    /**
     * @var array
     */
    private $readables = [];

    /**
     * @var array
     */
    private $writables = [];

    /**
     * @var callable
     */
    private $errorHandler;

    /**
     * ReactEventLoop constructor.
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->errorHandler = function ($e) {
            throw $e;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->running = true;
        $this->loop->run();
        $this->running = false;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        $this->loop->stop();
        $this->running = false;
    }

    /**
     * {@inheritdoc}
     */
    public function defer(callable $callback, $data = null)
    {
        $watcher = new Watcher();
        $watcher->id = $this->nextId++;
        $watcher->type = Watcher::DEFER;
        $watcher->callback = $callback;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;

        if (count($this->defers) === 0) {
            $this->setDeferFutureTick();
        }

        $this->defers[$watcher->id] = $watcher->id;

        return $watcher->id;
    }

    protected function setDeferFutureTick()
    {
        $this->loop->futureTick(function () {
            foreach ($this->defers as $watcherId) {
                if (!isset($this->watchers[$watcherId]) || !$this->watchers[$watcherId]->enabled) {
                    continue;
                }

                $callback = $this->watchers[$watcherId]->callback;
                $data = $this->watchers[$watcherId]->data;

                unset(
                    $this->watchers[$watcherId],
                    $this->defers[$watcherId]
                );

                try {
                    $callback($watcherId, $data);
                } catch (\Throwable $e) {
                    $this->errorHandler($e);
                } catch (\Exception $e) {
                    $this->errorHandler($e);
                }
            }

            if (count($this->defers) !== 0) {
                foreach ($this->defers as $watcherId) {
                    if (!isset($this->watchers[$watcherId])) {
                        continue;
                    }

                    if ($this->watchers[$watcherId]->referenced === true) {
                        $this->setDeferFutureTick();
                        break;
                    }
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delay($delay, callable $callback, $data = null)
    {
        if ($delay < 0) {
            throw new InvalidArgumentException('Delay must be greater than or equal to zero');
        }

        $watcher = new Watcher();
        $watcher->id = $this->nextId++;
        $watcher->type = Watcher::DELAY;
        $watcher->callback = $callback;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;

        $this->delayed[$watcher->id] = $this->loop->addTimer($delay / 1000, function (TimerInterface $timer) use ($watcher) {
            if (!isset($this->watchers[$watcher->id])) {
                $timer->cancel();
            }

            if (isset($this->watchers[$watcher->id]) && $this->watchers[$watcher->id]->enabled && $this->watchers[$watcher->id]->referenced) {
                $callback = $this->watchers[$watcher->id]->callback;
                $data = $this->watchers[$watcher->id]->data;

                unset(
                    $this->watchers[$watcher->id],
                    $this->delayed[$watcher->id]
                );

                try {
                    $callback($watcher->id, $data);
                } catch (\Throwable $e) {
                    $this->errorHandler($e);
                } catch (\Exception $e) {
                    $this->errorHandler($e);
                }
            }
        });

        return $watcher->id;
    }

    /**
     * {@inheritdoc}
     */
    public function repeat($interval, callable $callback, $data = null)
    {
        $watcher = new Watcher();
        $watcher->id = $this->nextId++;
        $watcher->type = Watcher::REPEAT;
        $watcher->callback = $callback;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;

        $this->repeats[$watcher->id] = $this->loop->addPeriodicTimer($interval / 1000, function (TimerInterface $timer) use ($watcher) {
            if (!isset($this->watchers[$watcher->id])) {
                $timer->cancel();

                unset(
                    $this->watchers[$watcher->id],
                    $this->delayed[$watcher->id]
                );
            }

            if ($this->watchers[$watcher->id]->enabled && $this->watchers[$watcher->id]->referenced) {
                $callback = $this->watchers[$watcher->id]->callback;
                $data = $this->watchers[$watcher->id]->data;

                try {
                    $callback($watcher->id, $data);
                } catch (\Throwable $e) {
                    $this->errorHandler($e);
                } catch (\Exception $e) {
                    $this->errorHandler($e);
                }
            }
        });

        return $watcher->id;
    }

    /**
     * {@inheritdoc}
     */
    public function onReadable($stream, callable $callback, $data = null)
    {
        $watcher = new Watcher();
        $watcher->id = $this->nextId++;
        $watcher->type = Watcher::READABLE;
        $watcher->callback = $callback;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;

        $this->readables[$watcher->id] = $stream;
        $this->addReadStream($watcher);
        return $watcher->id;
    }

    protected function addReadStream(Watcher $watcher)
    {
        $this->loop->addReadStream(
            $this->readables[$watcher->id],
            function () use ($watcher) {
                if (!isset($this->watchers[$watcher->id]) || !$this->watchers[$watcher->id]->enabled || !$this->watchers[$watcher->id]->referenced) {
                    return;
                }

                $callback = $this->watchers[$watcher->id]->callback;
                $data = $this->watchers[$watcher->id]->data;

                try {
                    $callback($watcher->id, $this->readables[$watcher->id], $data);
                } catch (\Throwable $e) {
                    $this->errorHandler($e);
                } catch (\Exception $e) {
                    $this->errorHandler($e);
                }
            }
        );

    }

    /**
     * {@inheritdoc}
     */
    public function onWritable($stream, callable $callback, $data = null)
    {
        $watcher = new Watcher();
        $watcher->id = $this->nextId++;
        $watcher->type = Watcher::WRITABLE;
        $watcher->callback = $callback;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;

        $this->writables[$watcher->id] = $stream;
        $this->addWriteStream($watcher);
        return $watcher->id;
    }

    protected function addWriteStream(Watcher $watcher)
    {
        $this->loop->addWriteStream(
            $this->writables[$watcher->id],
            function () use ($watcher) {
                if (!isset($this->watchers[$watcher->id]) || !$this->watchers[$watcher->id]->enabled || !$this->watchers[$watcher->id]->referenced) {
                    return;
                }

                $callback = $this->watchers[$watcher->id]->callback;
                $data = $this->watchers[$watcher->id]->data;

                try {
                    $callback($watcher->id, $this->writables[$watcher->id], $data);
                } catch (\Throwable $e) {
                    $this->errorHandler($e);
                } catch (\Exception $e) {
                    $this->errorHandler($e);
                }
            }
        );

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

        $this->watchers[$watcherId]->enabled = true;

        // Sort to execute in right order
        if ($this->watchers[$watcherId]->type === Watcher::DEFER) {
            unset($this->defers[$watcherId]);
            $this->defers[$watcherId] = $watcherId;
        }

        if (in_array($watcherId, $this->defers)) {
            $this->setDeferFutureTick();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disable($watcherId)
    {
        if (!isset($this->watchers[$watcherId])) {
            return;
        }

        $this->watchers[$watcherId]->enabled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel($watcherId)
    {
        unset($this->watchers[$watcherId]);

        if (isset($this->defers[$watcherId])) {
            unset($this->defers[$watcherId]);
        }
        if (isset($this->delayed[$watcherId])) {
            $this->delayed[$watcherId]->cancel();
            unset($this->delayed[$watcherId]);
        }
        if (isset($this->repeats[$watcherId])) {
            $this->repeats[$watcherId]->cancel();
            unset($this->repeats[$watcherId]);
        }
        if (isset($this->readables[$watcherId])) {
            $this->loop->removeReadStream($this->readables[$watcherId]);
            unset($this->readables[$watcherId]);
        }
        if (isset($this->writables[$watcherId])) {
            $this->loop->removeWriteStream($this->writables[$watcherId]);
            unset($this->writables[$watcherId]);
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

        $this->watchers[$watcherId]->referenced = true;

        if (in_array($watcherId, $this->defers)) {
            $this->setDeferFutureTick();
        }
        if (in_array($watcherId, $this->readables)) {
            $this->addReadStream($this->watchers[$watcherId]);
        }
        if (in_array($watcherId, $this->writables)) {
            $this->addWriteStream($this->watchers[$watcherId]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unreference($watcherId)
    {
        if (!isset($this->watchers[$watcherId])) {
            throw new InvalidWatcherException();
        }

        $this->watchers[$watcherId]->referenced = false;

        if (isset($this->delayed[$watcherId])) {
            $this->delayed[$watcherId]->cancel();
        }
        if (isset($this->repeats[$watcherId])) {
            $this->repeats[$watcherId]->cancel();
        }
        if (isset($this->readables[$watcherId])) {
            $this->loop->removeReadStream($this->readables[$watcherId]);
        }
        if (isset($this->writables[$watcherId])) {
            $this->loop->removeWriteStream($this->writables[$watcherId]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorHandler(callable $callback = null)
    {
        $this->errorHandler = $callback;
    }

    protected function errorHandler($e)
    {
        $errorHandler = $this->errorHandler;
        $errorHandler($e);
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        $watchers = [
            'referenced'   => 0,
            'unreferenced' => 0,
        ];

        $defer = $delay = $repeat = $onReadable = $onWritable = $onSignal = [
            'enabled'  => 0,
            'disabled' => 0,
        ];

        foreach ($this->watchers as $watcher) {
            switch ($watcher->type) {
                case Watcher::READABLE: $array = &$onReadable; break;
                case Watcher::WRITABLE: $array = &$onWritable; break;
                case Watcher::SIGNAL:   $array = &$onSignal; break;
                case Watcher::DEFER:    $array = &$defer; break;
                case Watcher::DELAY:    $array = &$delay; break;
                case Watcher::REPEAT:   $array = &$repeat; break;

                default: throw new \DomainException('Unknown watcher type');
            }

            if ($watcher->enabled) {
                ++$array['enabled'];

                if ($watcher->referenced) {
                    ++$watchers['referenced'];
                } else {
                    ++$watchers['unreferenced'];
                }
            } else {
                ++$array['disabled'];
            }
        }

        return [
            'watchers'    => $watchers,
            'defer'       => $defer,
            'delay'       => $delay,
            'repeat'      => $repeat,
            'on_readable' => $onReadable,
            'on_writable' => $onWritable,
            'on_signal'   => $onSignal,
            'running'     => $this->running,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getHandle()
    {
        return;
    }
}
