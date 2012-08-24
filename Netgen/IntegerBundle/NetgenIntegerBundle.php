<?php

namespace Netgen\IntegerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenIntegerBundle extends Bundle
{
    public function getParent()
    {
        return 'eZDemoBundle';
    }
}
