<?php

namespace Greg\Support\Db\Sql\Storage\Sqlite\Adapter;

class Pdo extends \Greg\Support\Db\Sql\Storage\Adapter\Pdo
{
    protected $stmtClass = Pdo\Stmt::class;

    public function __construct($path)
    {
        parent::__construct('sqlite:' . $path);

        return $this;
    }

    static public function create($appName, $path)
    {
        return static::newInstanceRef($appName, $path);
    }
}