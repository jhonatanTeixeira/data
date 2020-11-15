<?php

namespace Vox\Data;

use PHPUnit\Framework\TestCase;
use Vox\Data\Formatter\JsonFormatter;
use Vox\Metadata\Factory\MetadataFactoryFactory;

class SerializerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $mf = (new MetadataFactoryFactory())->createAnnotationMetadataFactory();
        $this->serializer = new Serializer(new ObjectExtractor($mf), new ObjectHydrator($mf));
        $this->serializer->registerFormat(new JsonFormatter());
    }

    public function testShouldSerializeToJson() {
        [$data, $expected] = $this->createData();

        $this->assertEquals($expected, $this->serializer->serialize('json', $data));
        $this->assertEquals($data, $this->serializer->deserialize('json', SerializeStub::class, $expected));
    }

    public function createData() {
        return [
            new SerializeStub(10, 'jhon', 33),
            '{"id":10,"name":"jhon","age":33}'
        ];
    }
}

class SerializeStub {
    private int $id;

    private string $name;

    private int $age;

    public function __construct(int $id, string $name, int $age)
    {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}