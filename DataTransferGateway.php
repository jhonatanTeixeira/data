<?php

namespace Vox\Data;

use Metadata\MetadataFactoryInterface;
use Vox\Metadata\ClassMetadata;

/**
 * transfer data from one object to another
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
class DataTransferGateway implements DataTransferGatewayInterface
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
    
    public function transferDataTo($fromObject, $toObject)
    {
        $this->objectGraphBuilder->clear();
        
        $toObject     = $this->objectGraphBuilder->buildObjectGraph($toObject);
        $metadataFrom = $this->getObjectMetadata($fromObject);
        $metadataTo   = $this->getObjectMetadata($toObject);
        
        /* @var $propertyMetadata \Vox\Metadata\PropertyMetadata */
        foreach ($metadataFrom->propertyMetadata as $propertyMetadata) {
            $bindings = $propertyMetadata->getAnnotation(Mapping\Bindings::class);
            $target   = $bindings->target ?? $propertyMetadata->name;
            
            $targetValue = $this->propertyAccessor->get($toObject, $target);
            
            if (is_object($targetValue)) {
                $this->transferDataTo($propertyMetadata->getValue($fromObject), $targetValue);
                continue;
            }
            
            $this->propertyAccessor->set($toObject, $target, $propertyMetadata->getValue($fromObject));
        }
        
        return $toObject;
    }
    
    public function transferDataFrom($fromObject, $toObject)
    {
        $this->objectGraphBuilder->clear();
        
        $toObject     = $this->objectGraphBuilder->buildObjectGraph($toObject);
        $metadataTo   = $this->getObjectMetadata($toObject);
        
        /* @var $propertyMetadata \Vox\Metadata\PropertyMetadata */
        foreach ($metadataTo->propertyMetadata as $propertyMetadata) {
            $bindings = $propertyMetadata->getAnnotation(Mapping\Bindings::class);
            $source   = $bindings->source ?? $propertyMetadata->name;
            $type     = $propertyMetadata->type;
            
            if (class_exists($type)) {
                $this->transferDataFrom($fromObject, $propertyMetadata->getValue($toObject));
                continue;
            }

            $sourceValue = $this->propertyAccessor->get($fromObject, $source);
            $propertyMetadata->setValue($toObject, $sourceValue);
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
