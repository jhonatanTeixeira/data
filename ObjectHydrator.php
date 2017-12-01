<?php

namespace Vox\Data;

use DateTime;
use Metadata\MetadataFactoryInterface;
use RuntimeException;
use Vox\Data\Mapping\Bindings;
use Vox\Data\Mapping\Discriminator;
use Vox\Metadata\ClassMetadata;
use Vox\Metadata\PropertyMetadata;

class ObjectHydrator implements ObjectHydratorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;
    
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }
    
    public function hydrate($object, array $data)
    {
        $objectMetadata = $this->getObjectMetadata($object, $data);

        /* @var $propertyMetadata PropertyMetadata  */
        foreach ($objectMetadata->propertyMetadata as $propertyMetadata) {
            $annotation = $propertyMetadata->getAnnotation(Bindings::class);
            $source     = $annotation ? ($annotation->source ?? $propertyMetadata->name) : $propertyMetadata->name;
            $type       = $propertyMetadata->type;
            
            if (!isset($data[$source])) {
                continue;
            }
            
            $value = $data[$source];
            
            if ($type && $value) {
                if ($this->isDecorated($type)) {
                    $value = $this->convertDecorated($type, $value);
                } elseif ($this->isNativeType($type)) {
                    $value = $this->convertNativeType($type, $value);
                } else {
                    $value = $this->convertObjectValue($type, $value);
                }
            }

            $propertyMetadata->setValue($object, $value);
        }
    }
    
    private function isNativeType(string $type)
    {
        return in_array($type, [
            'string',
            'array',
            'int',
            'integer',
            'float',
            'boolean',
            'bool',
            'DateTime',
            '\DateTime',
        ]);
    }
    
    private function isDecorated(string $type): bool
    {
        return (bool) preg_match('/(.*)\<(.*)\>/', $type);
    }
    
    private function convertNativeType($type, $value)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'string':
                return (string) $value;
            case 'boolean':
            case 'bool':
                return (bool) $value;
            case 'array':
                if (!is_array($value)) {
                    throw new RuntimeException('value is not array');
                }
                
                return $value;
            case 'DateTime':
            case '\DateTime':
                return new DateTime($value);
            default:
                return $value;
        }
    }
    
    private function convertDecorated(string $type, $value)
    {
        preg_match('/(.*)\<(.*)\>/', $type, $matches);
        
        list(, $type, $decoration) = $matches;
            
        switch ($type) {
            case 'array':
                if (!is_array($value)) {
                    throw new RuntimeException('value mapped as array is not array');
                }

                $data = [];

                foreach ($value as $item) {
                    $object = $this->convertObjectValue($decoration, $item);

                    $data[] = $object;
                }

                break;
            case 'DateTime':
            case '\DateTime':
                $data = DateTime::createFromFormat($decoration, $value);
                break;
        }

        return $data;
    }
    
    private function convertObjectValue(string $type, array $data)
    {
        $metadata = $this->getObjectMetadata($type, $data);
        $object   = $metadata->reflection->newInstanceWithoutConstructor();

        $this->hydrate(
            $object, 
            $data
        );

        return $object;
    }
    
    private function getObjectMetadata($object, array $data): ClassMetadata
    {
        $metadata      = $this->metadataFactory->getMetadataForClass(is_string($object) ? $object : get_class($object));
        $discriminator = $metadata->getAnnotation(Discriminator::class);

        if ($discriminator instanceof Discriminator && isset($data[$discriminator->field])) {
            if (!isset($discriminator->map[$data[$discriminator->field]])) {
                throw new RuntimeException("no discrimination for {$data[$discriminator->field]}");
            }

            $type     = $discriminator->map[$data[$discriminator->field]];
            $metadata = $this->metadataFactory->getMetadataForClass($type);
        }
        
        return $metadata;
    }
}