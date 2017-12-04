<?php

namespace Vox\Data;

use Metadata\MetadataFactoryInterface;
use Vox\Metadata\ClassMetadata;

class DataTransferGateway
{
    /**
     * @var ObjectGraphBuilderInterface
     */
    private $objectGraphBuilder;
    
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;
    
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    
    public function __construct(
        ObjectGraphBuilderInterface $objectGraphBuilder,
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->objectGraphBuilder = $objectGraphBuilder;
        $this->metadataFactory    = $metadataFactory;
        $this->propertyAccessor   = $propertyAccessor;
    }
    
    public function transferData($fromObject, $toObject)
    {
        $this->objectGraphBuilder->clear();
        
        $toObject     = $this->objectGraphBuilder->buildObjectGraph($toObject);
        $metadataFrom = $this->getObjectMetadata($fromObject);
        $metadataTo   = $this->getObjectMetadata($toObject);
        
        /* @var $propertyMetadata \Vox\Metadata\PropertyMetadata */
        foreach ($metadataFrom->propertyMetadata as $propertyMetadata) {
            $path = $propertyMetadata->getAnnotation(Mapping\Bindings::class)->target ?? $propertyMetadata->name;
            
//            if (!isset($metadataTo->propertyMetadata[$path])) {
//                throw new \RuntimeException("property {$metadataTo->name}:\${$path} does not exists");
//            }
            
            $targetValue = $this->propertyAccessor->get($toObject, $path);
            
            if (is_object($targetValue)) {
                $this->transferData($propertyMetadata->getValue($fromObject), $targetValue);
                continue;
            }
            
            $this->propertyAccessor->set($toObject, $path, $propertyMetadata->getValue($fromObject));
        }
        
        return $toObject;
    }
    
    private function getObjectMetadata($class): ClassMetadata
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        return $this->metadataFactory->getMetadataForClass($class);
    }
}
