<?php

namespace Vox\Data\Mapping;

/**
 * polimorfism discriminator
 * 
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("CLASS")
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
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

    public function __construct(array $map = null, string $field = 'type')
    {
        $this->map = $map;
        $this->field = $field;
    }
}