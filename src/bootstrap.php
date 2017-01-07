<?php

namespace WyriHaximus\React\AsyncInteropLoop;

use AsyncInterop\Loop;

Loop::setFactory(ReactDriverFactory::createFactory());
