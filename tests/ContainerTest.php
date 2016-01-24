<?php
use Idealogica\InDI;
use Interop\Container;
use Interop\Container\Exception;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    protected $c = null;

    protected function setUp()
    {
        $this->c = new InDI\Container();
    }

    public function testValues()
    {
        $this->runIOTest('test_value');
        $this->runIOTest(['test_value']);
        $this->runIOTest(69);
        $this->runIOTest(function () {});
        try
        {
            $this->c->get('nonexistsent_id');
            self::fail();
        }
        catch (Exception\NotFoundException $e) {}
    }

    public function testFactoryValues()
    {
        foreach($this->buildDefinitions() as $idx => $definition)
        {
            $id = 'id'.$idx;
            $this->c->addFactory($id, $definition);
            self::assertInstanceOf('Closure', $this->c->get($id));
        }
        foreach($this->c as $id => $callable)
        {
            $res1 = $callable('arg1', 'arg2');
            $res2 = $callable('arg1', 'arg2');
            self::assertEquals($res1->var, 'pass');
            self::assertFalse($res1 === $res2);
        }
    }

    public function testSharedValues()
    {
        foreach($this->buildDefinitions() as $idx => $definition)
        {
            $id = 'id'.$idx;
            $this->c->addShared($id, $definition);
            self::assertInstanceOf('Closure', $this->c->get($id));
        }
        foreach($this->c as $id => $callable)
        {
            $res1 = $callable('arg1', 'arg2');
            $res2 = $callable();
            self::assertEquals($res1->var, 'pass');
            self::assertTrue($res1 === $res2);
        }
    }

    public function testCallables()
    {
        foreach($this->buildDefinitions() as $idx => $definition)
        {
            $id = 'id'.$idx;
            $this->c->addShared($id, $definition);
            $res = $this->c->$id('arg1', 'arg2');
            self::assertEquals($res->var, 'pass');
        }
        $this->c->add('id', 'string_value');
        try
        {
            $this->c->id();
            self::fail();
        }
        catch(Exception\ContainerException $e) {}
    }

    public function testRemove()
    {
        $this->runIOTest();
        unset($this->c['id1']);
        self::assertFalse($this->c->has('id1'));
        unset($this->c->id2);
        self::assertFalse($this->c->has('id2'));
        $this->c->remove('id3');
        self::assertFalse($this->c->has('id3'));
    }

    public function testValueProvider()
    {
        $provider = new ValueProvider();
        $this->c->register($provider, 'id1', 'id2');
        $this->c->register(function ($argument)
        {
            self::assertEquals($this->get('id1'), 'test_value');
            self::assertEquals($this->get('id2'), 'test_value');
            $this->add($argument, 'test_value');
        }, 'id3');
        self::assertEquals($this->c->get('id3'), 'test_value');
    }

    public function testMasterDelegate()
    {
        $c1 = $this->c;
        $this->runIOTest();
        $c2 = new InDI\Container($c1);
        try
        {
            $c2->add('id3', 'new_value');
            self::fail();
        }
        catch(Exception\ContainerException $e) {}
        $c2->add('id4', 'new_value');
        self::assertEquals($c1->get('id1'), 'test_value');
        self::assertEquals($c1->get('id2'), 'test_value');
        self::assertEquals($c1->get('id3'), 'test_value');
        self::assertFalse($c1->has('id4'));
        $c2->addShared('id5', function () use ($c2)
        {
            self::assertTrue($this === $c2);
            self::assertEquals($this->get('id1'), 'test_value');
            self::assertEquals($this->get('id2'), 'test_value');
            self::assertEquals($this->get('id3'), 'test_value');
            self::assertEquals($this->get('id4'), 'new_value');
        });
        $c2->id5();
    }

    public function testLookupDelegate()
    {
        $c1 = $this->c;
        $this->runIOTest();
        $c2 = new InDI\Container($c1, InDI\DELEGATE_LOOKUP);
        $c2->add('id3', 'new_value');
        $c2->add('id4', 'new_value');
        self::assertFalse($c2->has('id1'));
        self::assertFalse($c2->has('id2'));
        self::assertEquals($c2->get('id3'), 'new_value');
        self::assertEquals($c2->get('id4'), 'new_value');
        $c2->addShared('id5', function () use ($c1)
        {
            self::assertTrue($this === $c1);
            self::assertEquals($this->get('id1'), 'test_value');
            self::assertEquals($this->get('id2'), 'test_value');
            self::assertEquals($this->get('id3'), 'test_value');
            self::assertFalse($this->has('id4'));
        });
        $c2->id5();
    }

    public function testCount()
    {
        $this->runIOTest();
        self::assertEquals(count($this->c), 3);
    }

    public function testIncorrectDefinitions()
    {
        foreach($this->buildIncorrectDefinitions() as $definition)
        {
            try
            {
                $this->c->addFactory('id', 'azazazaza');
                self::fail();
            }
            catch(TypeError $e) {}
        }
    }

    public function testIncorrectDefinitionArguments()
    {
        foreach($this->buildDefinitionsWithIncorrectArguments() as $idx => $definition)
        {
            $factoryId = 'factory'.$idx;
            $this->c->addFactory($factoryId, $definition);
            $sharedId = 'shared_object'.$idx;
            $this->c->addShared($sharedId, $definition);
            try
            {
               $this->c->$factoryId();
               self::fail();
            }
            catch(Exception\ContainerException $e)
            {
                echo("\n\n".$e->getMessage());
            }
            try
            {
               $this->c->$sharedId();
               self::fail();
            }
            catch(Exception\ContainerException $e) {}
        }
        echo("\n\n");
    }

    public function testPhpIsCallable()
    {
        foreach($this->buildDefinitions() as $idx => $definition)
        {
            self::assertTrue(is_callable($definition), $idx);
        }
    }

    public function testPhpIdAsPrivateMember()
    {
        $this->c->add('values', 'test_value');
        self::assertEquals($this->c->get('values'), 'test_value');
    }

    protected function runIOTest($value = 'test_value')
    {
        $testGetters = function ($id) use ($value)
        {
            self::assertTrue($this->c->has($id));
            self::assertEquals($this->c->get($id), $value);
            self::assertEquals($this->c[$id], $value);
            self::assertEquals($this->c->$id, $value);
            $found = false;
            foreach($this->c as $id => $object)
            {
                if($id === $id && $object === $value)
                {
                    $found = true;
                }
            }
            self::assertTrue($found);
        };
        $this->c['id1'] = $value;
        $testGetters('id1');
        $this->c->id2 = $value;
        $testGetters('id1');
        $testGetters('id2');
        $this->c->add('id3', $value);
        $testGetters('id1');
        $testGetters('id2');
        $testGetters('id3');
    }

    protected function buildDefinitions()
    {
        $definitions = [];
        $definitions[] = 'defineValue';
        $definitions[] = new DefinitionProvider;
        $definitions[] = [new DefinitionProvider, 'define'];
        $definitions[] = 'DefinitionProvider::defineStatic';
        $definitions[] = ['DefinitionProvider', 'defineStatic'];
        $definitions[] = function (Container\ContainerInterface $c)
        {
            if($this instanceof InDI\Container)
            {
                $obj = new stdClass();
                $obj->var = 'pass';
                return $obj;
            }
        };
        $definitions[] = new class
        {
            public function __invoke(Container\ContainerInterface $c, $arg1)
            {
                if($arg1 === 'arg1')
                {
                    $obj = new stdClass();
                    $obj->var = 'pass';
                    return $obj;
                }
            }
        };
        return $definitions;
    }

    protected function buildIncorrectDefinitions()
    {
        $definitions = [];
        $definitions[] = 'azazazaza';
        $definitions[] = new stdClass();
        $definitions[] = [new DefinitionProvider, 'azazazaza'];
        $definitions[] = 'DefinitionProvider::azazazaza';
        $definitions[] = ['DefinitionProvider', 'azazazaza'];
        return $definitions;
    }

    protected function buildDefinitionsWithIncorrectArguments()
    {
        $definitions = [];
        $definitions[] = 'badDefineValue';
        $definitions[] = new BadDefinitionProvider;
        $definitions[] = [new BadDefinitionProvider, 'define'];
        $definitions[] = 'BadDefinitionProvider::defineStatic';
        $definitions[] = ['BadDefinitionProvider', 'defineStatic'];
        $definitions[] = function (Azazaza $a) {};
        $definitions[] = new class
        {
            public function __invoke(Azazaza $a) {}
        };
        return $definitions;
    }
}
