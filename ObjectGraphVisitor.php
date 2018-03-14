<?php

namespace Vox\Data;

use InvalidArgumentException;
use Metadata\MetadataFactoryInterface;
use Traversable;
use Vox\Metadata\PropertyMetadata;

class ObjectGraphVisitor implements ObjectGraphVisitorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;
    
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    
    /**
     * @var ObjectVisitorInterface[]
     */
    private $visitors = [];
    
    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->metadataFactory  = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
    }
    
    public function visit($object, array &$context = [])
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('can only visit objects');
        }
        
        $this->doVisit($object, $context);
        $context['visited'][] = $object;
        
        $objectMetadata = $this->metadataFactory->getMetadataForClass(get_class($object));
        
        /* @var $propertyMetadata PropertyMetadata */
        foreach ($objectMetadata->propertyMetadata as $propertyMetadata) {
            $value = $this->propertyAccessor 
                ? $this->propertyAccessor->get($object, $propertyMetadata->name) 
                : $propertyMetadata->getValue($object);
            
            if (is_array($value) || $value instanceof Traversable) {
                $this->visitCollection($value, $context);
                
                continue;
            }
            
            if (!is_object($value)) {
                continue;
            }
            
            $this->visit($value, $context);
        }
    }
    
    private function visitCollection($collection, array &$context)
    {
        foreach ($collection as $item) {
            if (!is_object($item) || in_array($item, $context['visited'] ?? [], true)) {
                continue;
            }

            $this->visit($item, $context);
        }
    }
    
    private function doVisit($object, array &$context)
    {
        foreach ($this->visitors as $visitor) {
            if (in_array($object, $context['visited'] ?? [], true) || !$visitor->canVisit($object)) {
                continue;
            }

            $visitor->visit($object, $context);
        }
    }
    
    public function addVisitor(ObjectVisitorInterface $visitor): ObjectGraphVisitorInterface
    {
        $this->visitors[] = $visitor;
        
        return $this;
    }
}
