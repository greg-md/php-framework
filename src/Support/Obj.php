<?php

namespace Greg\Support;

use Greg\Storage\ArrayObject;
use Greg\Storage\ArrayReference;

class Obj
{
    const PROP_APPEND = 'append';

    const PROP_PREPEND = 'prepend';

    const PROP_REPLACE = 'replace';

    static public function loadInstance($className, ...$args)
    {
        return static::loadInstanceArgs($className, $args);
    }

    /**
     * @param $className
     * @param array $args
     * @return object
     */
    static public function loadInstanceArgs($className, array $args = [])
    {
        $class = new \ReflectionClass($className);

        $self = $class->newInstanceWithoutConstructor();

        if (method_exists($self, '__bind')) {
            $self->__bind();
        }

        if ($constructor = $class->getConstructor()) {
            $constructor->invokeArgs($self, $args);
        }

        return $self;
    }

    static public function fetchRef($value)
    {
        return ($value instanceof ArrayReference) ? $value->get() : $value;
    }

    /**
     * @param $return
     * @param $var
     * @param $value
     * @return mixed|$this
     */
    static public function &fetchVar($return, &$var, $value = null)
    {
        if (func_num_args() > 2) {
            $var = $value;

            return $return;
        }

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param null $value
     * @param string $type
     * @return int|float|bool|string|$this
     */
    static public function &fetchScalarVar($return, &$var, $value = null, $type = self::PROP_REPLACE)
    {
        if (func_num_args() > 2) {
            switch ($type) {
                case static::PROP_APPEND:
                    $var .= $value;

                    break;
                case static::PROP_PREPEND:
                    $var = $value . $var;

                    break;
                case static::PROP_REPLACE:
                    $var = is_scalar($value) ? $value : (string)$value;

                    break;
            }

            return $return;
        }

        $var = is_scalar($var) ? $var : (string)$var;

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param $value
     * @param string $type
     * @return string|$this
     */
    static public function &fetchStrVar($return, &$var, $value = null, $type = self::PROP_REPLACE)
    {
        if (func_num_args() > 2) {
            switch ($type) {
                case static::PROP_APPEND:
                    $var .= $value;

                    break;
                case static::PROP_PREPEND:
                    $var = $value . $var;

                    break;
                case static::PROP_REPLACE:
                    $var = (string)$value;

                    break;
            }

            return $return;
        }

        $var = (string)$var;

        return $var;
    }

    static public function &fetchBoolVar($return, &$var, $value = null)
    {
        if (func_num_args() > 2) {
            $var = (bool)$value;

            return $return;
        }

        $var = (bool)$var;

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param bool $unsigned
     * @param $value
     * @return int|$this
     */
    static public function &fetchIntVar($return, &$var, $unsigned = false, $value = null)
    {
        if (func_num_args() > 3) {
            $var = static::fetchInt($value, $unsigned);

            return $return;
        }

        $var = static::fetchInt($var, $unsigned);

        return $var;
    }

    static public function fetchInt($var, $unsigned = false)
    {
        return (int)(($unsigned and $var < 0) ? 0 : $var);
    }

    /**
     * @param $return
     * @param $var
     * @param bool $unsigned
     * @param $value
     * @return float|$this
     */
    static public function &fetchFloatVar($return, &$var, $unsigned = false, $value = null)
    {
        if (func_num_args() > 3) {
            $var = static::fetchFloat($value, $unsigned);

            return $return;
        }

        $var = static::fetchFloat($var, $unsigned);

        return $var;
    }

    static public function fetchFloat($var, $unsigned = false)
    {
        return (float)(($unsigned and $var < 0) ? 0 : $var);
    }

    /**
     * @param $return
     * @param $var
     * @param array $stack
     * @param $value
     * @param null $default
     * @return mixed|$this
     */
    static public function &fetchEnumVar($return, &$var, array $stack, $default = null, $value = null)
    {
        if (func_num_args() > 4) {
            $var = static::fetchEnum($value, $stack, $default);

            return $return;
        }

        $var = static::fetchEnum($var, $stack, $default);

        return $var;
    }

    static public function fetchEnum($var, array $stack, $default = null)
    {
        return in_array($var, $stack) ? $var : $default;
    }

    /**
     * @param $return
     * @param $var
     * @param callable $callable
     * @param $value
     * @return mixed|$this
     */
    static public function &fetchCallableVar($return, &$var, callable $callable, $value = null)
    {
        if (func_num_args() > 3) {
            $var = call_user_func_array($callable, [$value]);

            return $return;
        }

        $var = call_user_func_array($callable, [$var]);

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param null $key
     * @param null $value
     * @param string $type
     * @param bool $replace
     * @param bool $recursive
     * @return mixed|$this
     */
    static public function &fetchArrayObjVar($return, &$var, $key = null, $value = null, $type = self::PROP_APPEND, $replace = false, $recursive = false)
    {
        if (!($var instanceof ArrayObject)) {
            $var = new ArrayObject($var);
        }

        if (($num = func_num_args()) > 2) {
            if (is_array($key)) {
                $recursive = $replace;
                $replace = $type;
                $type = $value;

                if ($type === true) {
                    $type = static::PROP_REPLACE;
                }

                switch($type) {
                    case static::PROP_REPLACE:
                        $var->exchange($key);

                        break;
                    case static::PROP_APPEND:
                        if ($replace) {
                            if ($recursive) {
                                $var->replaceRecursiveMe($key);
                            } else {
                                $var->replaceMe($key);
                            }
                        } else {
                            if ($recursive) {
                                $var->mergeRecursiveMe($key);
                            } else {
                                $var->mergeMe($key);
                            }
                        }
                        break;
                    case static::PROP_PREPEND:
                        if ($replace) {
                            if ($recursive) {
                                $var->replacePrependRecursiveMe($key);
                            } else {
                                $var->replacePrependMe($key);
                            }
                        } else {
                            if ($recursive) {
                                $var->mergePrependRecursiveMe($key);
                            } else {
                                $var->mergePrependMe($key);
                            }
                        }
                        break;
                }

                return $return;
            }

            if ($num > 3) {
                switch($type) {
                    case static::PROP_REPLACE:
                        $var->set($key, $value);

                        break;
                    case static::PROP_PREPEND:
                        $var->prependKey($value, $key);

                        break;
                    case static::PROP_APPEND:
                        $var->appendKey($value, $key);

                        break;
                }

                return $return;
            }

            if ($var->has($key)) {
                return $var[$key];
            }

            $return = null;

            return $return;
        }

        return $var;
    }

    /**
     * @param $return
     * @param $var
     * @param null $key
     * @param null $value
     * @param string $type
     * @param bool $replace
     * @param bool $recursive
     * @return mixed|$this
     */
    static public function &fetchArrayVar($return, &$var, $key = null, $value = null, $type = self::PROP_APPEND, $replace = false, $recursive = false)
    {
        Arr::bringRef($var);

        if (($num = func_num_args() > 2)) {
            if (is_array($key)) {
                $recursive = $replace;
                $replace = $type;
                $type = $value;
                if ($type === null) {
                    $type = static::PROP_APPEND;
                }

                if ($type === true) {
                    $type = static::PROP_REPLACE;
                }

                switch($type) {
                    case static::PROP_REPLACE:
                        $var = $key;

                        break;
                    case static::PROP_APPEND:
                        if ($replace) {
                            if ($recursive) {
                                $var = array_replace_recursive($var, $key);
                            } else {
                                $var = array_replace($var, $key);
                            }
                        } else {
                            if ($recursive) {
                                $var = array_merge_recursive($var, $key);
                            } else {
                                $var = array_merge($var, $key);
                            }
                        }
                        break;
                    case static::PROP_PREPEND:
                        if ($replace) {
                            if ($recursive) {
                                $var = array_replace_recursive($key, $var);
                            } else {
                                $var = array_replace($key, $var);
                            }
                        } else {
                            if ($recursive) {
                                $var = array_merge_recursive($key, $var);
                            } else {
                                $var = array_merge($key, $var);
                            }
                        }
                        break;
                }

                return $return;
            }

            if ($num > 3) {
                switch($type) {
                    case static::PROP_REPLACE:
                        $var[$key] = $value;

                        break;
                    case static::PROP_PREPEND:
                        Arr::prependKey($var, $value, $key);

                        break;
                    case static::PROP_APPEND:
                        Arr::appendKey($var, $value, $key);

                        break;
                }

                return $return;
            }

            if (array_key_exists($key, $var)) {
                return $var[$key];
            }

            $return = null;

            return $return;
        }

        return $var;
    }
}