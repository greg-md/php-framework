<?php

namespace Greg\Support\Db\Sql;

use Greg\Engine\InternalTrait;

abstract class Storage implements StorageInterface
{
    use StorageTrait, InternalTrait;

    const PARAM_BOOL = 5;

    const PARAM_NULL = 0;

    const PARAM_INT = 1;

    const PARAM_STR = 2;

    const PARAM_LOB = 3;

    const PARAM_STMT = 4;

    const FETCH_ORI_NEXT = 0;
}