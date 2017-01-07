<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use AsyncInterop\Loop\DriverFactory;
use InvalidArgumentException;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

final class ReactDriverFactory implements DriverFactory
{
    /**
     * @var callable
     */
    private $factory;

    public static function createFactory()
    {
        return self::createFactoryFromLoop(Factory::create());
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
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $factory = $this->factory;
        return new ReactEventLoop($factory());
    }
}
