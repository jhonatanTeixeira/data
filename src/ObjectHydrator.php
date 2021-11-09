<?php

namespace Vox\Data;

use DateTime;
use Metadata\MetadataFactoryInterface;
use RuntimeException;
use Vox\Data\Mapping\Bindings;
use Vox\Data\Mapping\Discriminator;
use Vox\Data\Mapping\Exclude;
use Vox\Metadata\ClassMetadata;
use Vox\Metadata\PropertyMetadata;

/**
 * Hydrates objects based on its metadata information, uses data mapping
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
class ObjectHydrator implements ObjectHydratorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var TypeAwareObjectHydrator[]
     */
    private array $hydrators = [];
    
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function addHydrator(TypeAwareObjectHydrator $hydrator) {
        $this->hydrators[$hydrator->getSupportedClassName()] = $hydrator;
    }
    
    public function hydrate($object, array $data): object
    {
        if (is_string($object)) {
            $object = (new \ReflectionClass($object))->newInstanceWithoutConstructor();
        }

        $objectMetadata = $this->getObjectMetadata($object, $data);

        /* @var $propertyMetadata PropertyMetadata  */
        foreach ($objectMetadata->propertyMetadata as $propertyMetadata) {
            $annotation = $propertyMetadata->getAnnotation(Bindings::class);
            $source     = $annotation ? ($annotation->source ?? $propertyMetadata->name) : $propertyMetadata->name;
            $type       = $propertyMetadata->type;
            
            if (!isset($data[$source]) 
                || ($propertyMetadata->hasAnnotation(Exclude::class) 
                    && $propertyMetadata->getAnnotation(Exclude::class)->input)) {
                continue;
            }
            
            $value = $data[$source];

            if ($type && $value) {
                if ($propertyMetadata->isDecoratedType()) {
                    $value = $this->convertDecorated($type, $value);
                } elseif ($propertyMetadata->isNativeType()) {
                    $value = $this->convertNativeType($type, $value);
                } else {
                    if (!class_exists($type)) {
                        throw new \RuntimeException("type $type don't exists");
                    }
                    
                    $value = $this->convertObjectValue($type, $value);
                }
            }

            if ($propertyMetadata->hasSetter()) {
                $propertyMetadata->setter->invoke($object, [$value]);
            } else {
                $propertyMetadata->setValue($object, $value);
            }
        }

        return $object;
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
            case 'DateTimeImmutable':
            case '\DateTimeImmutable':
                return new \DateTimeImmutable($value);
            default:
                return $value;
        }
    }
    
    private function convertDecorated(string $type, $value)
    {
        preg_match('/(?P<class>.*)((\<(?P<decoration>.*)\>)|(?P<brackets>\[\]))/', $type, $matches);
        
        $class      = isset($matches['brackets']) ? 'array' : $matches['class'];
        $decoration = isset($matches['brackets']) ? $matches['class'] : $matches['decoration'];

        switch ($class) {
            case 'array':
                if (!is_array($value)) {
                    throw new RuntimeException('value mapped as array is not array');
                }

                $data = [];

                foreach ($value as $item) {
                    if (is_scalar($item) || is_bool($item) || $item instanceof DateTime) {
                        $value = $this->convertNativeType($decoration, $item);
                    } else {
                        $value = $this->convertObjectValue($decoration, $item);
                    }

                    $data[] = $value;
                }

                break;
            case 'DateTime':
            case '\DateTime':
                $data = DateTime::createFromFormat($decoration, $value);

                if (!$data) {
                    throw new RuntimeException("cannot convert date $value to format $decoration");
                }

                break;
            case 'DateTimeImmutable':
            case '\DateTimeImmutable':
                $data = \DateTimeImmutable::createFromFormat($decoration, $value);

                if (!$data) {
                    throw new RuntimeException("cannot convert date $value to format $decoration");
                }

                break;
        }

        return $data;
    }
    
    private function convertObjectValue(string $type, array $data)
    {
        $metadata = $this->getObjectMetadata($type, $data);
        $object   = $metadata->reflection->newInstanceWithoutConstructor();

        if (isset($this->hydrators[$type])) {
            return $this->hydrators[$type]->hydrate($object, $data);
        }

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
