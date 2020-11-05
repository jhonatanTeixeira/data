<?php

namespace Vox\Data;

interface ObjectVisitorInterface
{
    public function canVisit($object): bool;
    
    public function visit($object, array &$context);
}
