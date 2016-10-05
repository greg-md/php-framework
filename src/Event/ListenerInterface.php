<?php

namespace Greg\Event;

interface ListenerInterface
{
    public function on($event, callable $callable);

    public function register($event, $object);

    public function fire($event, ...$args);

    public function fireRef($event, &...$args);

    public function fireArgs($event, array $args = []);

    public function fireWith($event, ...$args);

    public function fireWithRef($event, &...$args);

    public function fireWithArgs($event, array $args = []);

    public function subscribe(SubscriberInterface $subscriber);
}
