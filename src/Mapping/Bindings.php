<?php

namespace Vox\Data\Mapping;

/**
 * maps the source and target of a single property
 * 
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY"})
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
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

    /**
     * @var string
     */
    public $group = 'default';

    public function __construct(string $source = null, string $target = null, string $from = null,
                                string $type = 'string', string $group = 'default')
    {
        $this->source = $source;
        $this->target = $target;
        $this->from = $from;
        $this->type = $type;
        $this->group = $group;
    }
}
