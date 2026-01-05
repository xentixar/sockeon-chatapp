<?php

namespace App\Repositories;

use App\Utils\Database;
use PDO;

class BaseRepository
{
    protected PDO $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }
}