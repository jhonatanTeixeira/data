<?php

namespace Vox\Data;

use Doctrine\Common\Annotations\AnnotationReader;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Vox\Data\Mapping\Bindings;
use Vox\Metadata\Driver\AnnotationDriver;

class DataTransferGatewayTest extends TestCase
{
    public function testShouldTransferDataToObject()
    {
        $metadataFactory  = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $propertyAccessor = new PropertyAccessor($metadataFactory);
        $graphBuilder     = new ObjectGraphBuilder($metadataFactory, $propertyAccessor);
        
        $gateway = new DataTransferGateway($graphBuilder, $metadataFactory, $propertyAccessor);
        
        $gatewayTest = new GatewayTestOne();
        
        $target = $gateway->transferDataTo($gatewayTest, GatewayTargetOne::class);
        
        $this->assertInstanceOf(GatewayTargetOne::class, $target);
        $this->assertInstanceOf(GatewayTargetTwo::class, $target->getTwo());
        $this->assertInstanceOf(GatewayTargetThree::class, $target->getThree());
        $this->assertEquals('one', $target->getName());
        $this->assertEquals('two', $target->getTwo()->getName());
        $this->assertEquals('three', $target->getThree()->getName());
    }
    
    public function testShouldTransferDataFromObject()
    {
        $metadataFactory  = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $propertyAccessor = new PropertyAccessor($metadataFactory);
        $graphBuilder     = new ObjectGraphBuilder($metadataFactory, $propertyAccessor);
        
        $gateway = new DataTransferGateway($graphBuilder, $metadataFactory, $propertyAccessor);
        
        $gatewayTest = new GatewayTestOne();
        
        $target = $gateway->transferDataFrom($gatewayTest, GatewayTargetOne::class);
        
        $this->assertInstanceOf(GatewayTargetOne::class, $target);
        $this->assertInstanceOf(GatewayTargetTwo::class, $target->getTwo());
        $this->assertInstanceOf(GatewayTargetThree::class, $target->getThree());
        $this->assertEquals('one', $target->getName());
        $this->assertEquals('two', $target->getTwo()->getName());
        $this->assertEquals('three', $target->getThree()->getName());
    }
}


class GatewayTestOne
{
    /**
     * @Bindings(target="name")
     */
    private $one = 'one';
    
    /**
     * @Bindings(target="two")
     * 
     * @var GatewayTestTwo
     */
    private $two;
    
    /**
     * @Bindings(target="three.name")
     * 
     * @var string
     */
    private $three = 'three';
    
    public function __construct()
    {
        $this->two = new GatewayTestTwo();
    }
}

class GatewayTestTwo
{
    private $name = 'two';
    
    
    public function getName()
    {
        return $this->name;
    }
}

class GatewayTargetOne
{
    /**
     * @Bindings(source="one")
     *
     * @var string
     */
    private $name;
    
    /**
     * @var GatewayTargetTwo
     */
    private $two;
    
    /**
     * @var GatewayTargetThree
     */
    private $three;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getTwo(): GatewayTargetTwo
    {
        return $this->two;
    }
    
    public function getThree(): GatewayTargetThree
    {
        return $this->three;
    }
}

class GatewayTargetTwo
{
    /**
     * @Bindings(source="two.name")
     *
     * @var type 
     */
    private $name;
    
    public function getName()
    {
        return $this->name;
    }
}

class GatewayTargetThree
{
    /**
     * @Bindings(source="three")
     *
     * @var string
     */
    private $name;
    
    public function getName()
    {
        return $this->name;
    }
}
