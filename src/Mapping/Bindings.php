<?php

namespace Vox\Data\Mapping;

/**
 * maps the source and target of a single property
 * 
 * @Annotation
 * @Target({"PROPERTY"})
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
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
