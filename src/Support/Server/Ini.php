<?php

namespace Greg\Support\Server;

class Ini
{
    static public function all($extension = null, $details = true)
    {
        return ini_get_all(...func_get_args());
    }

    static public function param($key = null, $value = null)
    {
        if ($num = func_num_args()) {
            if (is_array($key)) {
                foreach(($keys = $key) as $key => $value) {
                    static::set($key, $value);
                }

                return true;
            }

            if ($num > 1) {
                return static::set($key, $value);
            }

            return static::get($key);
        }

        return static::all();
    }

    static public function get($var)
    {
        if (($value = ini_get($var)) === false) {
            throw new \Exception('Server option `' . $var . '` does not exist.');
        }

        return $value;
    }

    static public function set($var, $value)
    {
        if (($oldValue = ini_set($var, $value)) === false) {
            throw new \Exception('Server option `' . $var . '` can not be set.');
        }

        return $oldValue;
    }
}