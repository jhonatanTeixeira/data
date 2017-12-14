<?php

namespace Vox\Data\Mapping;

/**
 * polimorfism discriminator
 * 
 * @Annotation
 * @Target("CLASS")
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
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