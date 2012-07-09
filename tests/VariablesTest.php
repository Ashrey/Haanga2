<?php

use Haanga2\Compiler\Dumper,
    Haanga2\Compiler\Parser\Term\String,
    Haanga2\Compiler\Parser\Term\Variable;

class VariablesTest extends \phpunit_framework_testcase
{
    public function s($v) 
    {
        return new String($v);
    }

    public function v($v, $m = NULL)
    {
        return new Variable($v, $m);
    }

    public function testDefaultBehaviour()
    {
        // foo['bar']['foobar']
        $vm  = new Dumper;
        $arr = new Variable("foo");
        $arr->addPart($this->s('bar'), Variable::T_ARRAY);
        $arr->addPart($this->s('foobar'), Variable::T_ARRAY);
        $this->assertEquals($arr->toString($vm), '$foo["bar"]["foobar"]');

        // foo[bar][foobar]
        $arr = new Variable("foo");
        $arr->addPart($this->v('bar'), Variable::T_ARRAY);
        $arr->addPart($this->v('foobar'), Variable::T_ARRAY);
        $this->assertEquals($arr->toString($vm), '$foo[$bar][$foobar]');

        // foo.bar.foobar
        $arr = new Variable("foo");
        $arr->addPart($this->v('bar'), Variable::T_DOT);
        $arr->addPart($this->v('foobar'), Variable::T_DOT);
        $this->assertEquals($arr->toString($vm), '$foo->bar->foobar');
    }

    public function testModifierArray()
    {
        $vm  = new Dumper;
        $mod = Variable::T_ARRAY;
        
        // @foo.bar.foobar
        $arr = new Variable("foo", $mod);
        $arr->addPart($this->v('bar', $mod), Variable::T_DOT);
        $arr->addPart($this->v('foobar', $mod), Variable::T_DOT);
        $this->assertEquals($arr->toString($vm), '$foo["bar"]["foobar"]');

        // @foo['bar']
        $arr = new Variable("foo", $mod);
        $arr->addPart($this->s('bar'), Variable::T_ARRAY);
        $this->assertEquals($arr->toString($vm), '$foo["bar"]');

        // @foo['bar']
        $arr = new Variable("foo", $mod);
        $arr->addPart($this->v('bar'), Variable::T_ARRAY);
        $this->assertEquals($arr->toString($vm), '$foo[$bar]');

        // @foo{bar}
        $arr = new Variable("foo", $mod);
        $arr->addPart($this->v('bar'), Variable::T_OBJECT);
        $this->assertEquals($arr->toString($vm), '$foo->{$bar}');
    }

}

