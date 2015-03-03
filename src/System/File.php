<?php

namespace Greg\System;

use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Support\Obj;

class File implements InternalInterface
{
    use Internal;

    protected $file = null;

    public function __construct($file)
    {
        $this->file($file);

        return $this;
    }

    public function ext()
    {
        return \Greg\Support\File::ext($this->file());
    }

    public function mime()
    {
        return \Greg\Support\File::mime($this->file());
    }

    public function file($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

}