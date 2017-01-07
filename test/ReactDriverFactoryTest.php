<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use AsyncInterop\Loop\Driver;
use PHPUnit_Framework_TestCase;
use React\EventLoop\StreamSelectLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;
use WyriHaximus\React\AsyncInteropLoop\ReactEventLoop;

class ReactDriverFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFactory()
    {
        $driver = ReactDriverFactory::createFactory()->create();
        $this->assertInstanceOf(
            Driver::class,
            $driver
        );
        $this->assertInstanceOf(
            ReactEventLoop::class,
            $driver
        );
    }

    public function testCreateFactoryFromLoop()
    {
        $driver = ReactDriverFactory::createFactoryFromLoop(StreamSelectLoop::class)->create();
        $this->assertInstanceOf(
            Driver::class,
            $driver
        );
        $this->assertInstanceOf(
            ReactEventLoop::class,
            $driver
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class "WyriHaximus\React\Tests\AsyncInteropLoop\ReactDriverFactoryTest" doesn't implement "React\EventLoop\LoopInterface"
     */
    public function testCreateFactoryFromLoopFailure()
    {
        ReactDriverFactory::createFactoryFromLoop(self::class)->create();
    }
}
