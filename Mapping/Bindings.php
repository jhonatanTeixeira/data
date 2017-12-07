<?php

namespace Vox\Data\Mapping;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Bindings
{
    /**
     * @var string
     */
    public $source;
    
    /**
     * @var string
     */
    public $target;
    
    /**
     * @var string
     */
    public $from;
    
    /**
     * @var string
     */
    public $type = 'string';
}
