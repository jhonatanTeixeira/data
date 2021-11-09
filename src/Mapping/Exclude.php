<?php

namespace Vox\Data\Mapping;

/**
 * marks a single property as excluded from the normalization or denormalization proccess
 * 
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY"})
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
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

    public function __construct(bool $input = true, bool $output = true)
    {
        $this->input = $input;
        $this->output = $output;
    }
}
