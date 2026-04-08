<?php

declare(strict_types=1);

namespace Models;

abstract class Model
{
    protected \PDO $pdo;
    protected static ?\PDO $staticPdo = null;

    public function __construct()
    {
        if (self::$staticPdo === null) {
            self::$staticPdo = new \PDO(
                "pgsql:host=db;port=5432;dbname=postgres",
                "semen",
                "0000",
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            );
        }

        $this->pdo = self::$staticPdo;
    }

    public static function getConnection(): \PDO
    {
        if (self::$staticPdo === null) {
            self::$staticPdo = new \PDO(
                "pgsql:host=db;port=5432;dbname=postgres",
                "semen",
                "0000",
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            );
        }

        return self::$staticPdo;
    }

    abstract protected static function getTableName(): string;
}
