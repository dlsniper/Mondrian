<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform;

use Trismegiste\Mondrian\Transform\ReflectionContext;

/**
 * ReflectionContextTest tests for ReflectionContext
 */
class ReflectionContextTest extends \PHPUnit_Framework_TestCase
{

    protected $context;

    protected function setUp()
    {
        $this->context = new ReflectionContext();
    }

    public function testEmpty()
    {
        $this->assertFalse($this->context->hasDeclaringClass('unknown'));
    }

    public function testInitClass()
    {
        $this->context->initSymbol('Some', false);
        $this->assertTrue($this->context->hasDeclaringClass('Some'));
        $this->assertFalse($this->context->isInterface('Some'));
    }

    public function testInitInterface()
    {
        $this->context->initSymbol('Some', true);
        $this->assertTrue($this->context->hasDeclaringClass('Some'));
        $this->assertTrue($this->context->isInterface('Some'));
    }

    public function testDeclarationSimple()
    {
        $this->context->initSymbol('Type', false);
        $this->context->addMethodToClass('Type', 'sample');
        $this->assertEquals('Type', $this->context->getDeclaringClass('Type', 'sample'));
        $this->context->resolveSymbol();
        $this->assertEquals('Type', $this->context->getDeclaringClass('Type', 'sample'));
    }

    public function testDeclarationParent()
    {
        $this->context->initSymbol('Class', false);
        $this->context->addMethodToClass('Class', 'sample');
        $this->context->initSymbol('Interface', true);
        $this->context->addMethodToClass('Interface', 'sample');
        $this->context->resolveSymbol();
        $this->assertEquals('Interface', $this->context->getDeclaringClass('Interface', 'sample'));
        $this->assertEquals('Class', $this->context->getDeclaringClass('Class', 'sample'));
        // add inheritance :
        $this->context->pushParentClass('Class', 'Interface');
        $this->context->resolveSymbol();
        $this->assertEquals('Interface', $this->context->getDeclaringClass('Interface', 'sample'));
        $this->assertEquals('Interface', $this->context->getDeclaringClass('Class', 'sample'));
    }

    public function testNeutralItemInheritance()
    {
        $this->context->initSymbol('Class', false);
        $this->context->addMethodToClass('Class', 'sample');
        $this->context->resolveSymbol();
        $this->assertEquals('Class', $this->context->findMethodInInheritanceTree('Class', 'sample'));
    }

    public function testSuperInheritance()
    {
        $this->context->initSymbol('Class', false);
        $this->context->initSymbol('Mother', false);
        $this->context->addMethodToClass('Mother', 'sample');
        $this->context->pushParentClass('Class', 'Mother');
        $this->context->resolveSymbol();
        $this->assertEquals('Mother', $this->context->findMethodInInheritanceTree('Class', 'sample'));
    }

    public function testOuterInheritance()
    {
        $this->context->initSymbol('Class', false);
        $this->context->addMethodToClass('Class', 'getIterator');
        $this->context->pushParentClass('Class', 'IteratorAggregate');
        $this->context->initSymbol('IteratorAggregate', true);
        $this->context->resolveSymbol();
        $this->assertEquals('IteratorAggregate', $this->context->getDeclaringClass('Class', 'getIterator'));
        $this->assertEquals('IteratorAggregate', $this->context->findMethodInInheritanceTree('Class', 'getIterator'));
    }

    public function testNotFoundMethod()
    {
        $this->context->initSymbol('Class', false);
        $this->context->resolveSymbol();
        $this->assertNull($this->context->findMethodInInheritanceTree('Class', 'unknown'));
    }

}
