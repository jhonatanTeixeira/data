<?php

namespace Vox\Data\Mapping;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Discriminator
{
    /** 
     * @var array<string> 
     */
    public $map;

    /** 
     * @var string 
     */
    public $field = 'type';
}