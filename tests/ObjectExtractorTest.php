<?php

namespace Vox\Data;

use PHPUnit\Framework\TestCase;
use Vox\Data\Mapping\Bindings;
use Vox\Data\Mapping\Exclude;
use Vox\Metadata\Factory\MetadataFactoryFactory;

class ObjectExtractorTest extends TestCase
{
    public function testShouldExtractData() {
        [$object, $expected] = $this->createData();

        $extractor = new ObjectExtractor(
            (new MetadataFactoryFactory())->createAnnotationMetadataFactory()
        );

        $this->assertEquals(
            $expected,
            $extractor->extract($object)
        );

        $context = ['extractType' => true];
        $data = $extractor->extract($object, $context);

        $this->assertEquals(FooData::class, $data['__type__']);

        $expected = [
            'id' => 10,
            'name' => 'def',
        ];

        $extractor->addExtractor(new class implements TypeAwareObjectExtractor {
            public function getSupportedClassName(): string
            {
                return FooData::class;
            }

            public function extract($object, array &$context = [])
            {
                return [
                    'id' => $object->getAbc(),
                    'name' => $object->getDef(),
                ];
            }
        });

        $this->assertEquals(
            $expected,
            $extractor->extract($object)
        );
    }

    function createData() {
        $barData = new BarData('jkl', new \DateTime('2020-10-10 10:10:10'));
        $barData2 = new BarData('kkk', new \DateTime('2020-10-11 11:11:11'));

        return [
            new FooData(10, "def", $barData, [$barData, $barData2], [$barData, $barData2]),
            [
                'abc' => 10,
                'def' => "def",
                'bar' => [
                    'jkl' => 'jkl',
                    'date' => '2020-10-10 10:10:10'
                ],
                'collection' => [
                    [
                        'jkl' => 'jkl',
                        'date' => '2020-10-10 10:10:10'
                    ],
                    [
                        'jkl' => 'kkk',
                        'date' => '2020-10-11 11:11:11'
                    ],
                ],
                'my_bars' => [
                    [
                        'jkl' => 'jkl',
                        'date' => '2020-10-10 10:10:10'
                    ],
                    [
                        'jkl' => 'kkk',
                        'date' => '2020-10-11 11:11:11'
                    ],
                ]
            ]
        ];
    }
}

class FooData {
    private int $abc;

    private string $def;

    /**
     * @Bindings(target="bar")
     */
    private BarData $ghi;

    /**
     * @var BarData[]
     */
    private array $collection;

    /**
     * @var array<BarData>
     * @Bindings(source="my_bars")
     */
    private array $collection2;

    /**
     * @Exclude()
     */
    private $excluded = 'excluded';

    public function __construct(int $abc, string $def, BarData $ghi, array $collection, array $collection2)
    {
        $this->abc = $abc;
        $this->def = $def;
        $this->ghi = $ghi;
        $this->collection = $collection;
        $this->collection2 = $collection2;
    }

    public function getAbc(): int
    {
        return $this->abc;
    }

    public function getDef(): string
    {
        return $this->def;
    }

    public function getGhi(): BarData
    {
        return $this->ghi;
    }

    public function getCollection(): array
    {
        return $this->collection;
    }

    public function getCollection2(): array
    {
        return $this->collection2;
    }
}

class BarData {
    private string $jkl;

    /**
     * @var \DateTime<Y-m-d H:i:s>
     */
    private \DateTime $date;

    public function __construct(string $jkl, \DateTime $date)
    {
        $this->jkl = $jkl;
        $this->date = $date;
    }
}