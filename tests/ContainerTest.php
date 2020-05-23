<?php
/** @noinspection PhpUndefinedMethodInspection */

use Idealogica\InDI;
use Psr\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class ContainerTest extends TestCase
{
    /**
     * @var InDI\Container|null
     */
    protected $c;

    /**
     *
     */
    protected function setUp()
    {
        $this->c = new InDI\Container();
    }

    /**
     * @throws InDI\Exception\ContainerException
     */
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
        } catch (NotFoundExceptionInterface $e) {}
    }

    /**
     * @throws InDI\Exception\ContainerException
     * @throws InDI\Exception\NotFoundException
     */
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
            self::assertEquals('pass', $res1->var);
            self::assertFalse($res1 === $res2);
        }
    }

    /**
     * @throws InDI\Exception\ContainerException
     * @throws InDI\Exception\NotFoundException
     */
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
            self::assertEquals('pass', $res1->var);
            self::assertTrue($res1 === $res2);
        }
    }

    /**
     * @throws InDI\Exception\ContainerException
     */
    public function testCallables()
    {
        foreach($this->buildDefinitions() as $idx => $definition)
        {
            $id = 'id'.$idx;
            $this->c->addShared($id, $definition);
            $res = $this->c->$id('arg1', 'arg2');
            self::assertEquals('pass', $res->var);
        }
        $this->c->add('id', 'string_value');
        try
        {
            $this->c->id();
            self::fail();
        } catch(ContainerExceptionInterface $e) {}
    }

    /**
     * @throws InDI\Exception\ContainerException
     */
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

    /**
     * @throws InDI\Exception\NotFoundException
     * @throws ReflectionException
     */
    public function testValueProvider()
    {
        $provider = new ValueProvider();
        $this->c->register($provider, 'id1', 'id2');
        $this->c->register(function ($argument)
        {
            ContainerTest::assertEquals('test_value', $this->get('id1'));
            ContainerTest::assertEquals('test_value', $this->get('id2'));
            $this->add($argument, 'test_value');
        }, 'id3');
        self::assertEquals('test_value', $this->c->get('id3'));
    }

    /**
     * @throws InDI\Exception\ContainerException
     * @throws InDI\Exception\NotFoundException
     */
    public function testMasterDelegate()
    {
        $c1 = $this->c;
        $this->runIOTest();
        $c2 = new InDI\Container($c1);
        try
        {
            $c2->add('id3', 'new_value');
            self::fail();
        } catch(ContainerExceptionInterface $e) {}
        $c2->add('id4', 'new_value');
        self::assertEquals('test_value', $c1->get('id1'));
        self::assertEquals('test_value', $c1->get('id2'));
        self::assertEquals('test_value', $c1->get('id3'));
        self::assertFalse($c1->has('id4'));
        $c2->addShared('id5', function () use ($c2)
        {
            ContainerTest::assertTrue($this === $c2);
            ContainerTest::assertEquals('test_value', $this->get('id1'));
            ContainerTest::assertEquals('test_value', $this->get('id2'));
            ContainerTest::assertEquals('test_value', $this->get('id3'));
            ContainerTest::assertEquals('new_value', $this->get('id4'));
        });
        $c2->id5();
    }

    /**
     * @throws InDI\Exception\ContainerException
     * @throws InDI\Exception\NotFoundException
     */
    public function testLookupDelegate()
    {
        $c1 = $this->c;
        $this->runIOTest();
        $c2 = new InDI\Container($c1, InDI\DELEGATE_LOOKUP);
        $c2->add('id3', 'new_value');
        $c2->add('id4', 'new_value');
        self::assertFalse($c2->has('id1'));
        self::assertFalse($c2->has('id2'));
        self::assertEquals('new_value', $c2->get('id3'));
        self::assertEquals('new_value', $c2->get('id4'));
        $c2->addShared('id5', function () use ($c1)
        {
            ContainerTest::assertTrue($this === $c1);
            ContainerTest::assertEquals('test_value', $this->get('id1'));
            ContainerTest::assertEquals('test_value', $this->get('id2'));
            ContainerTest::assertEquals('test_value', $this->get('id3'));
            ContainerTest::assertFalse($this->has('id4'));
        });
        $c2->id5();
    }

    /**
     * @throws InDI\Exception\ContainerException
     */
    public function testCount()
    {
        $this->runIOTest();
        self::assertCount(3, $this->c);
    }

    /**
     * @throws InDI\Exception\ContainerException
     * @doesNotPerformAssertions
     */
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

    /**
     * @throws InDI\Exception\ContainerException
     * @doesNotPerformAssertions
     */
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
            catch(ContainerExceptionInterface $e)
            {
                echo("\n\n".$e->getMessage());
            }
            try
            {
               $this->c->$sharedId();
               self::fail();
            }
            catch(ContainerExceptionInterface $e) {}
        }
        echo("\n\n");
    }

    /**
     *
     */
    public function testPhpIsCallable()
    {
        foreach($this->buildDefinitions() as $idx => $definition)
        {
            self::assertTrue(is_callable($definition), $idx);
        }
    }

    /**
     * @throws InDI\Exception\ContainerException
     * @throws InDI\Exception\NotFoundException
     */
    public function testPhpIdAsPrivateMember()
    {
        $this->c->add('values', 'test_value');
        self::assertEquals('test_value', $this->c->get('values'));
    }

    /**
     * @param string $value
     *
     * @throws InDI\Exception\ContainerException
     */
    protected function runIOTest($value = 'test_value')
    {
        $testGetters = function ($id) use ($value)
        {
            ContainerTest::assertTrue($this->c->has($id));
            ContainerTest::assertEquals($value, $this->c->get($id));
            ContainerTest::assertEquals($value, $this->c[$id]);
            ContainerTest::assertEquals($value, $this->c->$id);
            $found = false;
            foreach($this->c as $cid => $object)
            {
                if($id === $cid && $object === $value)
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
