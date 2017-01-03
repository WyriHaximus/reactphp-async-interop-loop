<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use Interop\Async\Loop;

Loop::setFactory(ReactDriverFactory::createFactory());
