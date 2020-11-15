<?php

namespace Vox\Data;

interface TypeAwareObjectHydrator extends ObjectHydratorInterface
{
    public function getSupportedClassName(): string;
}