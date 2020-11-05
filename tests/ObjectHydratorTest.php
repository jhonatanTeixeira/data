<?php

namespace Vox\Data;

use PHPUnit\Framework\TestCase;
use Vox\Data\Mapping\Bindings;
use Vox\Metadata\Factory\MetadataFactoryFactory;

class ObjectHydratorTest extends TestCase
{
    public function testShouldHydrateData() {
        $data = [
            'pk' => 10,
            'name' => 'Jhon Doe',
            'createdAt' => '1983-12-20 08:00:00',
            'styles' => $styles = ['fancy', 'casual'],
            'options' => $options = ['foo', 'bar'],
            'titles' => $titles = ['sir', 'master', 'doctor'],
            'canceledAt' => $canceled = '2020-12-12',
            'user_address' => [
                'id' => 20,
                'name' => 'My Address',
                'street' => 'Awesome Street'
            ]
        ];

        $hydrator = new ObjectHydrator((new MetadataFactoryFactory())->createAnnotationMetadataFactory());

        $user = $hydrator->hydrate(User::class, $data);
        $compareUser = new User(
            10,
            \DateTime::createFromFormat('Y-m-d H:i:s', '1983-12-20 08:00:00'),
            $styles,
            $options,
            $titles,
            new \DateTime($canceled),
            'Jhon Doe',
            new Address(20, 'Awesome Street')
        );
        $compareUser->getAddress()->setterCalled = true;

        $this->assertEquals($compareUser, $user);
        $this->assertTrue($user->getAddress()->setterCalled);
    }
}

class Person {
    /**
     * @Bindings(source="pk")
     */
    private int $id;

    /**
     * @var \DateTime<Y-m-d H:i:s>
     */
    private \DateTime $createdAt;

    private array $styles;

    /**
     * @var string[]
     */
    private $options;

    /**
     * @var array<string>
     */
    private $titles;

    private \DateTime $canceledAt;

    public function __construct(int $id, \DateTime $createdAt, array $styles, array $options, array $titles,
                                \DateTime $canceledAt)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->styles = $styles;
        $this->options = $options;
        $this->titles = $titles;
        $this->canceledAt = $canceledAt;
    }


    public function getId()
    {
        return $this->id;
    }
}

class User extends Person {
    private string $name;

    /**
     * @Bindings(source="user_address")
     */
    private Address $address;

    public function __construct(int $id, \DateTime $createdAt, array $styles, array $options, array $titles,
                                \DateTime $canceledAt, string $name, Address $address)
    {
        parent::__construct($id, $createdAt, $styles, $options, $titles, $canceledAt);
        $this->name = $name;
        $this->address = $address;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}

class Address {
    private int $id;

    private string $street;

    public bool $setterCalled = false;

    public function __construct(int $id, string $street)
    {
        $this->id = $id;
        $this->street = $street;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
        $this->setterCalled = true;
    }
}