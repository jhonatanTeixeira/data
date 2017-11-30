<?php

namespace Vox\Data;

interface PropertyAccessorInterface
{
    public function get($object, string $name);
    
    public function set($object, string $name, $value);
}
