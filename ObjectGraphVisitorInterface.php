<?php

namespace Vox\Data;

interface ObjectGraphVisitorInterface
{
    public function visit($object, array &$context = []);
    
    public function addVisitor(ObjectVisitorInterface $visitor): ObjectGraphVisitorInterface;
}
