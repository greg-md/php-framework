<?php

namespace Greg\Config\Ini;

use Greg\Config\Ini;
use Greg\Support\Obj;

class File extends Ini
{
    protected $file = null;

    public function __construct($file = null, $section = null, $indexDelimiter = null)
    {
        if ($file) {
            $this->file($file);
        }
        $file = $this->file();

        return parent::__construct($file ? parse_ini_file($file, true) : null, $section, $indexDelimiter);
    }

    static public function create($appName, $file = null, $section = null, $indexDelimiter = null)
    {
        return static::newInstanceRef($appName, $file, $section, $indexDelimiter);
    }

    static public function fetch($file, $section = null, $indexDelimiter = false)
    {
        return parent::fetchContents(parse_ini_file($file, true), $section, $indexDelimiter);
    }

    public function file($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}