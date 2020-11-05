<?php

namespace Vox\Data;

use Doctrine\Common\Annotations\AnnotationReader;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Vox\Metadata\Driver\AnnotationDriver;

class ObjectGraphBuilderTest extends TestCase
{
    public function testShouldBuildGraph()
    {
        $metadataFactory  = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $propertyAccessor = new PropertyAccessor($metadataFactory);
        
        $builder = new ObjectGraphBuilder($metadataFactory, $propertyAccessor);
        
        $object = $builder->buildObjectGraph(GraphRoot::class);
        
        $this->assertInstanceOf(GraphRoot::class, $object);
        $this->assertInstanceOf(GraphDependencyOne::class, $object->getOne());
        $this->assertInstanceOf(GraphDependencyTwo::class, $object->getTwo());
        $this->assertInstanceOf(GraphDependencyOne::class, $object->getTwo()->getOne());
        $this->assertInstanceOf(GraphDependencyTwo::class, $object->getOne()->getTwo());
        $this->assertInstanceOf(GraphDependencyThree::class, $object->getThree());
    }
}


class GraphRoot
{
    /**
     * @var GraphDependencyOne
     */
    private $one;
    
    /**
     * @var GraphDependencyTwo
     */
    private $two;
    
    private $three;
    
    public function __construct(GraphDependencyTwo $two)
    {
        $this->two = $two;
    }
    
    public function getOne(): GraphDependencyOne
    {
        return $this->one;
    }

    public function getTwo(): GraphDependencyTwo
    {
        return $this->two;
    }
    
    public function getThree()
    {
        return $this->three;
    }

    public function setThree(GraphDependencyThree $three)
    {
        $this->three = $three;
        
        return $this;
    }
}

class GraphDependencyOne
{
    /**
     * @var GraphDependencyTwo
     */
    private $two;
    
    public function __construct(GraphDependencyTwo $two)
    {
        $this->two = $two;
    }
    
    public function getTwo(): GraphDependencyTwo
    {
        return $this->two;
    }
}

class GraphDependencyTwo
{
    /**
     * @var GraphDependencyOne
     */
    private $one;
    
    public function getOne()
    {
        return $this->one;
    }

    public function setOne($one)
    {
        $this->one = $one;
        
        return $this;
    }
}

class GraphDependencyThree
{
    
}