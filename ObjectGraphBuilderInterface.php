<?php

namespace Vox\Data;

interface ObjectGraphBuilderInterface
{
    public function buildObjectGraph($object);
    
    public function clear();
}
