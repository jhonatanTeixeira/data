<?php

namespace Vox\Data;

interface ObjectHydratorInterface
{
    public function hydrate($object, array $data);
}
