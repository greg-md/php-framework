<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Support\Arr;

class Resources
{
    use Accessor, Internal;

    public function __construct(array $resources = [])
    {
        $this->addMore($resources);

        return $this;
    }

    static public function create($appName, array $resources = [])
    {
        return static::newInstanceRef($appName, $resources);
    }

    public function addMore(array $resources)
    {
        foreach($resources as $name => $class) {
            $this->add($name, $class);
        }

        return $this;
    }

    public function add($name, $class)
    {
        $this->storage[$name] = Arr::bring($class);

        return $this;
    }

    public function getClasses()
    {
        $classNames = [];

        foreach($this->storage as $name => $class) {
            $classNames[$name] = $class[0];
        }

        return $classNames;
    }

    public function get($name)
    {
        $resource = $this->memory('resource/' . $name);

        if (!$resource) {
            if (!Arr::has($this->storage, $name)) {
                throw Exception::newInstance($this->appName(), 'Undefined resource `' . $name . '`.');
            }

            $resource = $this->app()->loadInstance(...$this->storage[$name]);

            $this->memory('resource/' . $name, $resource);
        }

        return $resource;
    }
}