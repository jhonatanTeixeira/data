<?php


namespace Vox\Data;


use Metadata\MetadataFactoryInterface;
use Vox\Data\Mapping\Bindings;
use Vox\Data\Mapping\Exclude;
use Vox\Metadata\PropertyMetadata;

class ObjectExtractor implements ObjectExtractorInterface
{
    private MetadataFactoryInterface $metadataFactory;

    /**
     * @var TypeAwareObjectExtractor[]
     */
    private array $extractors = [];

    private string $defaultDateFormat;

    public function __construct(MetadataFactoryInterface $metadataFactory, string $defaultDateFormat = 'Y-m-d H:i:s')
    {
        $this->metadataFactory = $metadataFactory;
        $this->defaultDateFormat = $defaultDateFormat;
    }

    public function addExtractor(TypeAwareObjectExtractor $extractor) {
        $this->extractors[$extractor->getSupportedClassName()] = $extractor;
    }

    /**
     * @return array|string|null
     */
    public function extract($object, array &$context = []) {
        if (is_iterable($object)) {
            return $this->extractIterable($object, $context);
        }

        $context['storage'] ??= new \SplObjectStorage();

        if ($context['storage']->contains($object)) {
            return $context['storage'][$object];
        }

        $context['storage']->attach($object);

        if (isset($this->extractors[get_class($object)])) {
            return $this->extractors[get_class($object)]->extract($object, $context);
        }

        if ($object instanceof \DateTime) {
            return $this->extractDate($object, $context);
        }

        $data = [];

        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));

        /* @var $property PropertyMetadata */
        foreach($metadata->propertyMetadata as $property) {
            if ($property->hasAnnotation(Exclude::class)) {
                continue;
            }

            $context['property'] = $property;
            $value = $property->hasGetter() ? $property->getter->invoke($object) : $property->getValue($object);

            $name = $property->hasAnnotation(Bindings::class)
                ? $property->getAnnotation(Bindings::class)->target
                    ?? $property->getAnnotation(Bindings::class)->source
                    ?? $property->name
                : $property->name;

            $data[$name] = !is_scalar($value) ? $this->extract($value, $context) : $value;
        }

        if ($context['extractType'] ?? false) {
            $data[$context['typeField'] ?? '__type__'] = get_class($object);
        }

        $context['storage'][$object] = $data;

        return $data;
    }

    protected function extractDate(\DateTime $date, array &$context) {
        /* @var $property PropertyMetadata */
        $property = $context['property'];
        $format = $this->defaultDateFormat;

        if ($property->isDateType() && $property->isDecoratedType()) {
            $format = $property->typeInfo['decoration'];
        }

        return $date->format($format);
    }

    protected function extractIterable(iterable $collection, array &$context): array {
        $extracted = [];

        foreach ($collection as $index => $item) {
            if (is_iterable($item)) {
                $this->extractIterable($item, $context);
            } elseif (is_object($item)) {
                $extracted[$index] = $this->extract($item, $context);
            } else {
                $extracted[$index] = $item;
            }
        }

        return $extracted;
    }
}