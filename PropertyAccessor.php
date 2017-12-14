<?php

namespace Vox\Data;

use Metadata\MetadataFactoryInterface;
use RuntimeException;

/**
 * Accesses objects data through setters, getters and reflection
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
class PropertyAccessor implements PropertyAccessorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;
    
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }
    
    public function get($object, string $name)
    {
        if (preg_match('/\./', $name)) {
            $properties = explode('.', $name);
            $name       = array_pop($properties);
            
            foreach ($properties as $property) {
                $object = $this->get($object, $property);
            }
        }
        
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));
        
        $getterName = sprintf('get%s', ucfirst($name));
        
        if (isset($metadata->methodMetadata[$getterName])) {
            return $metadata->methodMetadata[$getterName]->invoke($object);
        }
        
        if (!isset($metadata->propertyMetadata[$name])) {
            throw new RuntimeException("property $name doesn't exists on {$metadata->name}");
        }
        
        return $metadata->propertyMetadata[$name]->getValue($object);
    }

    public function set($object, string $name, $value)
    {
        if (preg_match('/\./', $name)) {
            $properties = explode('.', $name);
            $name       = array_pop($properties);
            
            foreach ($properties as $property) {
                $object = $this->get($object, $property);
            }
        }
        
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));
        
        $setterName = sprintf('set%s', ucfirst($name));
        
        if (isset($metadata->methodMetadata[$setterName])) {
            $metadata->methodMetadata[$setterName]->invoke($object, [$value]);
            
            return;
        }
        
        if (!isset($metadata->propertyMetadata[$name])) {
            throw new RuntimeException("property $name doesn't exists on {$metadata->name}");
        }
        
        $metadata->propertyMetadata[$name]->setValue($object, $value);
    }
}
