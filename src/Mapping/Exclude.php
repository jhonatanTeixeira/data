<?php

namespace Vox\Data\Mapping;

/**
 * marks a single property as excluded from the normalization or denormalization proccess
 * 
 * @Annotation
 * @Target({"PROPERTY"})
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
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
