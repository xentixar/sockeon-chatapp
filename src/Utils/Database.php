<?php

namespace App\Utils;

use PDO;

class Database
{
    private static PDO $database;

    public static function init(): void
    {
        self::$database = new PDO('sqlite:' . __DIR__ . "/../../data/database.sqlite");
        self::runInitialMigrations();
    }

    public static function getInstance(): PDO
    {
        return self::$database;
    }

    private static function runInitialMigrations(): void
    {
        self::getInstance()->exec('CREATE TABLE IF NOT EXISTS users(
            id integer PRIMARY KEY AUTOINCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL UNIQUE,
            password varchar(255) NOT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP
        )');

        self::getInstance()->exec('CREATE TABLE IF NOT EXISTS personal_access_tokens(
            id integer PRIMARY KEY AUTOINCREMENT,
            token varchar(255) NOT NULL,
            user_id integer REFERENCES users(id),
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP
        )');

        self::getInstance()->exec('CREATE TABLE IF NOT EXISTS messages(
            id integer PRIMARY KEY AUTOINCREMENT,
            message text NOT NULL,
            user_id integer REFERENCES users(id),
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP
        )');
    }
}