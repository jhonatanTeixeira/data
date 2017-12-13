<?php

namespace Vox\Data\Mapping;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Exclude
{
    /**
     * @var bool
     */
    public $input = true;
    
    /**
     * @var bool
     */
    public $output = true;
}
