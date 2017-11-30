<?php

namespace Vox\Data;

use Metadata\MetadataFactoryInterface;
use ReflectionClass;
use ReflectionParameter;
use Vox\Metadata\ClassMetadata;
use Vox\Metadata\PropertyMetadata;

class ObjectGraphBuilder implements ObjectGraphBuilderInterface
{
    private $storage = [];
    
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;
    
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    
    private $visited = [];

    public function __construct(MetadataFactoryInterface $metadataFactory, PropertyAccessorInterface $propertyAccessor)
    {
        $this->metadataFactory  = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
    }
    
    /**
     * @param object $object
     */
    public function buildObjectGraph($object)
    {
        if (is_string($object)) {
            $object = $this->createObject($object);
        }
        
        /* @var $metadata ClassMetadata */
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));
        
        /* @var $propertyMetadata PropertyMetadata */
        foreach ($metadata->propertyMetadata as $propertyMetadata) {
            $type = $propertyMetadata->type;
            
            if (!class_exists($type)) {
                continue;
            }
            
            $dependency = $this->fetchObject($type) ?? $this->createObject($type);
            
            if (!in_array($type, $this->visited)) {
                $this->visited[] = $type;
                $this->buildObjectGraph($dependency);
            }
            
            $this->propertyAccessor->set($object, $propertyMetadata->name, $dependency);
        }
        
        return $object;
    }
    
    private function createObject($className)
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        
        /* @var $reflection ReflectionClass */
        $reflection = $metadata->reflection;
        
        $params = [];
        
        if ($constructor = $reflection->getConstructor()) {
            /* @var $injectable ReflectionParameter */
            foreach ($constructor->getParameters() as $injectable) {
                $typeReflection = $injectable->getClass();

                if (!$typeReflection) {
                    continue;
                }

                $object = $this->fetchObject($typeReflection->name) ?? $this->createObject($typeReflection->name);

                $this->storeObject($object);

                $params[] = $object;
            }
        }
        
        return $reflection->newInstanceArgs($params);
    }
    
    private function storeObject($object)
    {
        $this->storage[get_class($object)] = $object;
    }
    
    private function fetchObject(string $objectName)
    {
        return $this->storage[$objectName] ?? null;
    }

    public function clear()
    {
        $this->storage = $this->visited = [];
        
        return $this;
    }
}
