<?php

namespace Vox\Data;

use Countable;
use Doctrine\Common\Annotations\AnnotationReader;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Vox\Metadata\Driver\AnnotationDriver;

class ObjectGraphVisitorTest extends TestCase
{
    public function testShouldVisitAllObjects()
    {
        $visitor = new class implements ObjectVisitorInterface {
            public function canVisit($object): bool
            {
                return $object instanceof AbstractNode && !$object instanceof Intermediate;
            }
            
            public function visit($object, array &$context)
            {
                $object->visit();
            }
        };
        
        $objectGraphVisitor = new ObjectGraphVisitor(new MetadataFactory(new AnnotationDriver(new AnnotationReader())));
        $objectGraphVisitor->addVisitor($visitor);
        
        $root = new Root();
        
        $objectGraphVisitor->visit($root);
        
        $this->assertCount(1, $root);
        $this->assertCount(1, $root->getNode());
        $this->assertCount(0, $root->getNode()->getIntermediate());
        $this->assertCount(1, $root->getNode()->getLeaves()[0]);
        $this->assertCount(1, $root->getNode()->getLeaves()[1]);
    }
    
    public function testShouldVisitAllObjectsWithPropertyAccessor()
    {
        $visitor = new class implements ObjectVisitorInterface {
            public function canVisit($object): bool
            {
                return $object instanceof AbstractNode && !$object instanceof Intermediate;
            }
            
            public function visit($object, array &$context)
            {
                $object->visit();
            }
        };
        
        $mf = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $pa = new PropertyAccessor($mf);
        $objectGraphVisitor = new ObjectGraphVisitor($mf, $pa);
        $objectGraphVisitor->addVisitor($visitor);
        
        $root = new Root();
        
        $objectGraphVisitor->visit($root);
        
        $this->assertCount(1, $root);
        $this->assertCount(1, $root->getNode());
        $this->assertCount(0, $root->getNode()->getIntermediate());
        $this->assertCount(1, $root->getNode()->getLeaves()[0]);
        $this->assertCount(1, $root->getNode()->getLeaves()[1]);
    }
}


abstract class AbstractNode implements Countable
{
    private $visitCount = 0;
    
    public function visit()
    {
        $this->visitCount++;
    }
    
    public function count(): int
    {
        return $this->visitCount;
    }
}

class Root extends AbstractNode
{
    /**
     * @var Node
     */
    private $node;
    
    /**
     * @var string
     */
    private $name = 'root';
    
    public function __construct()
    {
        $this->node = new Node($this);
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getNode(): Node
    {
        return $this->node;
    }
}

class Node extends AbstractNode
{
    /**
     * @var Leaf[]
     */
    private $leaves;
    
    /**
     * @var AbstractNode
     */
    private $intermediate;
    
    public function __construct(Root $root)
    {
        $this->leaves = [
            new Leaf($root),
            new Leaf($root),
        ];
        
        $this->intermediate = new Intermediate();
    }
    
    public function getLeaves(): array
    {
        return $this->leaves;
    }
    
    public function getIntermediate(): Intermediate
    {
        return $this->intermediate;
    }
}

class Intermediate extends AbstractNode
{
    
}

class Leaf extends AbstractNode
{
    private $root;
    
    public function __construct(Root $root)
    {
        $this->root = $root;
    }
    
    public function getRoot(): Root
    {
        return $this->root;
    }
}