<?php
namespace Idealogica\InDI;

use Interop\Container as InteropContainer;

const DELEGATE_LOOKUP = true;
const DELEGATE_MASTER = false;

trait ContainerTrait
{
    /**
     * Master delegate.
     *
     * @var \Interop\Container\ContainerInterface
     */
    private $delegateMaster = null;

    /**
     * Lookup delegate.
     *
     * @var \Interop\Container\ContainerInterface
     */
    private $delegateLookup = null;

    /**
     * Container values.
     *
     * @var array
     */
    private $values = [];

    /**
     * Constructor.
     *
     * @param \Interop\Container\ContainerInterface $delegate
     * @param bool $delegateRole
     */
    public function __construct(
        InteropContainer\ContainerInterface $delegate = null,
        bool $delegateRole = DELEGATE_MASTER
    ) {
        if ($delegateRole === DELEGATE_MASTER) {
            $this->delegateMaster = $delegate;
        } else {
            $this->delegateLookup = $delegate;
        }
    }

    /**
     * Checks if value exists.
     *
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->delegateMaster && $this->delegateMaster->has($id) ?:
            isset($this->values[$id]);
    }

    /**
     * Gets value.
     *
     * @param string $id
     *
     * @return mixed
     * @throws Exception\NotFound
     */
    public function get($id)
    {
        if ($this->delegateMaster && $this->delegateMaster->has($id)) {
            $value = $this->delegateMaster->get($id);
        } elseif (isset($this->values[$id])) {
            $value = $this->values[$id];
        } else {
            throw new Exception\NotFound('Value with id "%s" was not found.', $id);
        }
        return $value;
    }

    /**
     * Adds value.
     *
     * @param string $id
     * @param mixed $value
     *
     * @return $this
     * @throws Exception\Container
     */
    public function add(string $id, $value)
    {
        if ($this->delegateMaster && $this->delegateMaster->has($id)) {
            throw new Exception\Container(
                'Value "%s" assignment will not take effect since master ' .
                'container have value with the same id.', $id
            );
        }
        $this->values[$id] = $value;
        return $this;
    }

    /**
     * Adds factory value.
     *
     * @param string $id
     * @param callable $definition
     *
     * @return $this
     */
    public function addFactory(string $id, callable $definition)
    {
        return $this->add(
            $id,
            function (...$arguments) use ($definition) {
                return $this->invoke($definition, ...$arguments);
            }
        );
    }

    /**
     * Adds shared value.
     *
     * @param string $id
     * @param callable $definition
     *
     * @return $this
     */
    public function addShared(string $id, callable $definition)
    {
        return $this->add(
            $id,
            function (...$arguments) use ($definition) {
                static $instance = null;
                if (!$instance) {
                    $instance = $this->invoke($definition, ...$arguments);
                }
                return $instance;
            }
        );
    }

    /**
     * Removes previously defined value.
     *
     * @param string $id
     *
     * @return $this
     */
    public function remove(string $id)
    {
        unset($this->values[$id]);
        return $this;
    }

    /**
     * Registers value provider.
     *
     * @param callable $provider
     * @param mixed $arguments,...
     *
     * @return $this
     */
    public function register(callable $provider, ...$arguments)
    {
        $this->invoke($provider, ...$arguments);
        return $this;
    }

    /**
     * Invokes given callable.
     *
     * @param callable $callable
     * @param mixed $arguments,...
     *
     * @return mixed
     */
    protected function invoke(callable $callable, ...$arguments)
    {
        $arguments = array_values($arguments);
        $container = $this->delegateLookup ?: $this;
        $containerInterface = 'Interop\\Container\\ContainerInterface';
        $refParameterExceptionHandler = function (
            \ReflectionException $e,
            \Reflector $refMethod
        ) use ($callable) {
            if ($callable instanceof \Closure) {
                $presentation = 'closure';
            } else if (is_object($callable)) {
                $presentation = 'invokable object ' . get_class($callable);
            } else if (is_array($callable)) {
                $presentation = (is_object($callable[0]) ?
                        get_class($callable[0]) :
                        $callable[0]) . '::' . $callable[1];
            } else {
                $presentation = $callable;
            }
            $presentation .= '() defined in ' . $refMethod->getFileName() .
                '(' . $refMethod->getStartLine() . '-' . $refMethod->getEndLine() . ')';
            throw new Exception\Container(
                'Argument of %s is not properly declared. %s',
                $presentation,
                $e->getMessage()
            );
        };
        if ($callable instanceof \Closure) {
            $callable = $callable->bindTo($container);
        } else if (is_object($callable)) {
            $callable = [$callable, '__invoke'];
        } else if (is_string($callable) && preg_match('/^([^:]+)::(.+)$/', $callable, $m)) {
            $callable = [$m[1], $m[2]];
        }
        if (is_array($callable)) {
            $refMethod = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $refMethod = new \ReflectionFunction($callable);
        }
        for ($i = 0; $i < $refMethod->getNumberOfParameters(); $i++) {
            $refClass = null;
            try {
                $refClass = (new \ReflectionParameter($callable, $i))->getClass();
            } catch (\ReflectionException $e) {
                $refParameterExceptionHandler($e, $refMethod);
            }
            if ($refClass) {
                $className = $refClass->getName();
                if ($className === $containerInterface ||
                    in_array($containerInterface, class_implements($className))
                ) {
                    array_splice($arguments, $i, 0, [$container]);
                }
            }
        }
        return $callable(...$arguments);
    }

    /**
     * Invokes callable form container.
     *
     * @param string $methodName
     * @param array $arguments
     *
     * @return mixed
     * @throws Exception\Container
     */
    public function __call(string $methodName, array $arguments)
    {
        $value = $this->get($methodName);
        if (is_callable($value)) {
            return $value(...$arguments);
        }
        throw new Exception\Container(
            'Value with id "%s" is not a callable (%s) and can not be executed.',
            $methodName,
            gettype($value)
        );
    }
}
