# InDI - [In]Dependency Injector

<br /><img alt="InDI" title="InDI" src="http://storage9.static.itmages.com/i/17/0704/h_1499205205_3962698_14415773fa.png"><br /><br />

[1. What is InDI?](#1-what-is-indi)<br />
[2. Installation](#2-installation)<br />
[3. Container values](#3-container-values)<br />
[4. Lazy initialization of container values](#4-lazy-initialization-of-container-values)<br />
[4.1. Defining shared values](#41-defining-shared-values)<br />
[4.2. Defining factory values](#42-defining-factory-values)<br />
[4.3. Accessing shared and factory values](#43-accessing-shared-and-factory-values)<br />
[5. Dependency injection](#5-dependency-injection)<br />
[6. Value providers](#6-value-providers)<br />
[7. Delegates](#7-delegates)<br />
[8. Integration](#8-integration)<br />
[9. License](#9-license)

## 1. What is InDI?

InDI is the simplest [in]dependency injection container for PHP7, compatible with
[PSR-11](https://github.com/container-interop/container-interop). It offers
intuitive way to manage PHP application dependencies mostly using native language constructs.
The main idea is to provide painless way for programmers of any level of experience to use
dependency injection in their projects. InDI is fast, easy to use, powerful and standards
compliant. It doesn't provide sort of magic with automatic constructor arguments resolution,
but I'm sure that it's a main advantage of InDI and such containers. Your code will always
be readable and clear.

##### What is dependency injection?

It's a pattern that allows you to manage relations between your services
and settings transparently. Using it you can build your application from a bunch of reusable decoupled
components, distribute your initial settings over your application, write clear code that
can be easily refactored, tested and maintained.
There is a good [explanatory article](http://fabien.potencier.org/what-is-dependency-injection.html)
from Fabien Potencier.

##### InDI - more simple than Pimple

InDI is inspired by [Pimple](http://pimple.sensiolabs.org/) - another great dependency
injection container for PHP. Their main principles are the same, so it won't take much of
your time to start using InDI if you are an experienced Pimple user.

## 2. Installation

```
composer require idealogica/indi:~1.0.0
```
InDI requires PHP7 and `container-interop/container-interop` package.

## 3. Container values

InDI is a simple key-value storage and you can add any kind of data to it.
Any PHP variable can be stored inside InDI:

```php
$container = new Idealogica\InDI\Container();

$container->value1 = 'string'; // using container property
$container['value2'] = ['array']; // using array notation
$container->add('value3', new stdClass()); // using container method
```

Values can be accessed in the similar way:

```php
var_export($container->value1); // 'string'
var_export($container['value2']); // array (0 => 'array',)
var_export($container->get('value3')); // stdClass::__set_state(array())
```

You can iterate over InDI values:

```php
foreach($container as $id => $value) {}
```

You can check that value exists:

```php
var_export(isset($container->value1)); // true
var_export(isset($container['value2'])); // true
var_export($container->has('value3')); // true
var_export(isset($container->value4)); // false
```

Values that you set previously can be removed:

```php
unset($container->value1);
unset($container['value2']);
$container->remove('value3');

```

## 4. Lazy initialization of container values

Let's assume that you have database connection service. Of course, you can add it to
container directly:

```php
$dbDriver = new DBAL\MySql('localhost', 'database');
$container->db = new DBAL\Connection($dbDriver, 'user', 'pass');
```
In this case database connection initializes instantly when you call `new` operator.
If you want on-demand connection to your database you should use value lazy definition.
*Value lazy definition* is a PHP callable that simply returns initialized value:

```php
// closure
function (): DBAL\Connection
{
    $dbDriver = new DBAL\MySql('localhost', 'database');
    return new DBAL\Connection($dbDriver, 'user', 'pass');
}

// function
function defineDB(): DBAL\Connection
{
    $dbDriver = new DBAL\MySql('localhost', 'database');
    return new DBAL\Connection($dbDriver, 'user', 'pass');
}

// static method
class DB
{
    public static function define(): DBAL\Connection
    {
        $dbDriver = new DBAL\MySql('localhost', 'database');
        return new DBAL\Connection($dbDriver, 'user', 'pass');
    }
}

// invokable object
new class
{
    public function __invoke(): DBAL\Connection
    {
        $dbDriver = new DBAL\MySql('localhost', 'database');
        return new DBAL\Connection($dbDriver, 'user', 'pass');
    }
}

// object method
[new class
{
    public function define(): DBAL\Connection
    {
        $dbDriver = new DBAL\MySql('localhost', 'database');
        return new DBAL\Connection($dbDriver, 'user', 'pass');
    }
}, 'define']
```

#### 4.1. Defining shared values

You can pass *value lazy definition* to `addShared` method of container to get
value shared across your application:

```php
// sharing service
$container->addShared('db', function (): DBAL\Connection
{
    $dbDriver = new DBAL\MySql('localhost', 'database');
    return new DBAL\Connection($dbDriver, 'user', 'pass');
});

// you can share any kind of data
$container->addShared('number', function (): int
{
    return rand(0, 5);
});
```
#### 4.2. Defining factory values

Use `addFactory` method along with *value lazy definition* to obtain a new value instance
every time you access it:

```php
// factory service
$container->addFactory('view', function (string $template, array $parms = []): View
{
    return (new View('path/to/templates'))->setTemplate(template)->setParms(parms);
});

// you can produce any kind of data
$container->addFactory('number', function (int $min, int $max): int
{
    return rand($min, $max);
});
```
Notice that additional arguments can be defined in factory *value lazy definition* and then
passed to it at runtime.

#### 4.3. Accessing shared and factory values

Your previously defined shared or factory values can be accessed in two different ways:

► Directly form container:

```php
// shared value
$db1 = $container->db(); // returns DBAL\Connection instance
$db2 = $container->db(); // returns the same DBAL\Connection instance
var_export($db1 === $db2); // true

// factory values
$view1 = $container->view('template', ['parm' => 'value']); // returns View instance
$view2 = $container->view('template', ['parm' => 'value']); // returns another View instance
var_export($view1 === view2); // false
```

► Using raw *value lazy definition* closure. Obtain it just like an any other regular value:

```php
$getDb = $container->db;
$getView = $container->view;
```
Later you can get shared or factory data by calling obtained closure:
```php
// shared value
$db1 = $getDb(); // returns DBAL\Connection instance
$db2 = $getDb(); // returns the same DBAL\Connection instance
var_export($db1 === $db2); // true

// factory values
$view1 = $getView('template', ['parm' => 'value']); // returns View instance
$view2 = $getView('template', ['parm' => 'value']); // returns another View instance
var_export($view1 === $view2); // false
```
It can be helpful when:
* You need for "laziest" initialization. For example you can pass this closure to your
middleware and get instance of `DBAL\Connection` right just before using it
* You want to get variable amount of instances of factory values in one place. For example you want to
have multiple view instances in the same middleware

## 5. Dependency injection

Let's define all initial settings for our database connection and view classes:

```php
$container->dbHost = 'localhost';
$container->dbDatabase = 'database';
$container->dbUser = 'user';
$container->dbPassword = 'pass';
$container->templatesPath = 'path/to/templates';
```

Of course, you can inject these values to service that was added directly:
```php
$container->dbDriver = new DBAL\MySql($container->dbHost, $container->dbDatabase);
$container->db = new DBAL\Connection($container->dbDriver, $container->dbUser, $container->dbPassword);
```

In case of lazy initialization you can inject any value from container in your *value lazy definition*.
When *value lazy definition* is closure `$this` can be used to access container:

```php
$container->addShared('dbDriver', function (): DBAL\MySql
{
    return new DBAL\MySql($this->dbHost, $this->dbDatabase);
});
$container->addShared('db', function (): DBAL\Connection
{
    return new DBAL\Connection($this->dbDriver(), $this->dbUser, $this->dbPassword);
});
```
Anyway, for all PHP callables InDI can detect instance of Interop\Container\ContainerInterface
in arguments and pass itself on its place:

```php
// shared value
$container->addShared('dbDriver', function (Interop\Container\ContainerInterface $container): DBAL\MySql
{
    new DBAL\MySql($container->dbHost, $container->dbDatabase);
});
$container->addShared('db', function (Idealogica\InDI\Container $container): DBAL\Connection
{
    return new DBAL\Connection($container->dbDriver(), $container->dbUser, $container->dbPassword);
});
$db = $container->db();

// factory values
$container->addFactory('view', function (
    string $template,
    Idealogica\InDI\Container $container,
    array $parms = []): View
{
    return (new View($container->templatesPath))->setTemplate(template)->setParms(parms);
});
$view = $container->view('template', ['parm' => 'value']);
```
Make sure that container argument is typehinted.

## 6. Value providers

If you want to create redistributable component and use it in different projects
you should define *value provider*. It's also just a PHP callable like a *value lazy definition*:

```php
class DbProvider
{
    public function __invoke(
        Interop\Container\ContainerInterface $container,
        string $host,
        string $database,
        string $user,
        string $password)
    {
        $container->addShared('dbDriver', function () use ($host, $database)
        {
            return new DBAL\MySql($host, $database);
        });
        $container->addShared('db', function () use ($user, $password)
        {
            return new DBAL\Connection($this->dbDriver(), $user, $password);
        });
    }
}

```
As you can see, you can add additional arguments to callable along with container instance.
Let's register our new *value provider* using container `register` method:

```php
// 'localhost', 'database', 'user', 'pass' are additional arguments of value provider
$container->register(new DbProvider(), 'localhost', 'database', 'user', 'pass');
```
*Value providers* are executed right after they are registered.

## 7. Delegates

InDI can interact with any PSR-11 compliant dependency injection container.
You can pass foreign container instance as a constructor argument to share values form it
in two modes:

► Master mode. Allows to have all values from foreign container available in InDI:

```php
if($foreignContainer instanceof Interop\Container\ContainerInterface)
{
    var_export($foreignContainer->has('dbDriver')); // true
    $container = new InDI\Container($foreignContainer);
    var_export($container->has('dbDriver')); // true
}
```

► Lookup mode. Allows to have all values from foreign container available as a dependency lookups:

```php
if($foreignContainer instanceof Interop\Container\ContainerInterface)
{
    var_export($foreignContainer->has('dbDriver')); // true
    $container = new Idealogica\InDI\Container($foreignContainer, Idealogica\InDI\DELEGATE_LOOKUP);
    var_export($container->has('dbDriver')); // false
    $container->addShared('db', function ()
    {
        var_export($this->has('dbDriver')); // true
    });
}
```
More details about delegate lookup feature you can find in
[PSR-11 documentation](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md).

## 8. Integration

It's possible to integrate InDI into your project in few different ways:

► Most common and simple - just create InDI container instance and then use it:

```php
$container = new Idealogica\InDI\Container();
```

► Inherit you main application class from `Idealogica\InDI\Container`:

```php
class MyApp extends Idealogica\InDI\Container {}
```

► In case when your main application class is already inherited you can use traits to
introduce InDI functionality:

```php
class MyApp implements Iterator, ArrayAccess, Countable, Interop\Container\ContainerInterface
{
    use Idealogica\InDI\ContainerTrait;
    use Idealogica\InDI\ArrayAccessTrait;
    use Idealogica\InDI\PropertyAccessTrait;
}
```

## 9. License

InDI is licensed under a [MIT License](https://opensource.org/licenses/MIT).
