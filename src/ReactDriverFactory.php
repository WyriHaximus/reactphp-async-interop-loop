<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop\DriverFactory;
use InvalidArgumentException;
use React\EventLoop\LoopInterface;

final class ReactDriverFactory implements DriverFactory
{
    /**
     * @var callable
     */
    private $facory;

    public static function createFactory()
    {
        return new self('React\EventLoop\Factory::create');
    }

    public static function createFactoryFromLoop($loop)
    {
        if (!is_subclass_of($loop, LoopInterface::class)) {
            throw new InvalidArgumentException('Class "' . $loop . '" doesn\'t implement "' . LoopInterface::class . '"');
        }

        return new self(function () use ($loop) {
            return new $loop();
        });
    }

    protected function __construct(callable $factory)
    {
        $this->facory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $facory = $this->facory;
        return new ReactEventLoop($facory());
    }
}
