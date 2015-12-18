<?php

namespace Greg\Event;

use Greg\Engine\InternalTrait;
use Greg\Storage\AccessorTrait;
use Greg\Tool\Arr;
use Greg\Tool\Str;

class Listener implements ListenerInterface
{
    use AccessorTrait, InternalTrait;

    public function __construct(array $events = [], array $subscribers = [])
    {
        $this->addMore($events);

        $this->addSubscribers($subscribers);

        return $this;
    }

    public function addMore(array $events)
    {
        foreach($events as $key => $event) {
            $name = array_shift($event);

            if (!$name) {
                throw new \Exception('Event name is required in listener.');
            }

            $call = array_shift($event);

            if (!$call) {
                throw new \Exception('Event caller is required in listener.');
            }

            $this->on($name, $call, array_shift($event));
        }

        return $this;
    }

    public function on($event, callable $call, $id = null)
    {
        if (!Arr::hasRef($this->storage, $event)) {
            $this->storage[$event] = new \ArrayIterator();
        }

        if ($id !== null) {
            $this->storage[$event][$id] = $call;
        } else {
            $this->storage[$event][] = $call;
        }

        return $this;
    }

    public function register($event, $class)
    {
        if (!is_object($class)) {
            throw new \Exception('Event registrar should be an object.');
        }

        foreach(Arr::bring($event) as $event) {
            $method = lcfirst(Str::phpName($event));

            if (!method_exists($class, $method)) {
                throw new \Exception('Method `' . $method . '` not found in class `' . get_class($class) . '`.');
            }

            $this->on($event, [$class, $method], get_class($class) . '::' . $method);
        }

        return $this;
    }

    public function fire($event, ...$args)
    {
        return $this->fireRef($event, ...$args);
    }

    public function fireRef($event, &...$args)
    {
        return $this->fireArgs($event, $args);
    }

    public function fireArgs($event, array $args = [])
    {
        if (Arr::hasRef($this->storage, $event)) {
            foreach($this->storage[$event] as $function) {
                $this->callCallable($function, ...$args);
            }
        }

        return $this;
    }

    public function fireWith($event, ...$args)
    {
        return $this->fireWithRef($event, ...$args);
    }

    public function fireWithRef($event, &...$args)
    {
        return $this->fireWithArgs($event, $args);
    }

    public function fireWithArgs($event, array $args = [])
    {
        if (Arr::hasRef($this->storage, $event)) {
            foreach($this->storage[$event] as $function) {
                $this->callCallableWith($function, ...$args);
            }

        }

        return $this;
    }

    public function addSubscribers($subscribers, callable $callback = null)
    {
        foreach($subscribers as $name => $subscriber) {
            $this->subscribe($name, $subscriber, $callback);
        }

        return $this;
    }

    public function subscribe($name, $subscriber, callable $callback = null)
    {
        if (is_string($subscriber)) {
            $subscriber = $this->loadClassInstance($subscriber);
        }

        if (!($subscriber instanceof SubscriberInterface)) {
            throw new \Exception('Subscriber `' . $name . '` should be an instance of Greg\Event\SubscriberInterface.');
        }

        $subscriber->subscribe($this);

        if ($callback) {
            $this->callCallableWith($callback, $subscriber);
        }

        return $this;
    }
}