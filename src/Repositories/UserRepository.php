<?php

namespace App\Repositories;

use PDO;
use Random\RandomException;

class UserRepository extends BaseRepository
{
    public function create(array $data): bool|array
    {
        $statement = $this->database->prepare('INSERT INTO users(name, email, password) VALUES(:name,:email,:password)');
        $statement->bindParam(':name', $data['name']);
        $statement->bindParam(':email', $data['email']);
        $statement->bindParam(':password', $data['password']);
        $statement->execute();
        return $this->findById($this->database->lastInsertId());
    }

    public function get(): array
    {
        $rows = $this->database->query('SELECT * FROM users');
        return $rows->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(string $field, string $value): array|bool
    {
        $statement = $this->database->prepare("SELECT * FROM users WHERE $field = :value");
        $statement->bindParam(':value', $value);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|bool
    {
        $statement = $this->database->prepare("SELECT * FROM users WHERE id=:id");
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @throws RandomException
     */
    public function createToken(int $userId): string|false
    {
        $user = $this->findById($userId);
        if (empty($user)) {
            return false;
        }

        $token = $this->generateSecureAccessToken();
        if ($this->database->query("INSERT INTO personal_access_tokens(token, user_id) VALUES('$token', $userId)")) {
            return $token;
        } else {
            return false;
        }
    }

    public function getUserByToken(string $token): array|false
    {
        $statement = $this->database->prepare("SELECT * FROM personal_access_tokens WHERE token= :token");
        $statement->bindParam(':token', $token);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (empty($row)) {
            return false;
        } else {
            return $this->findById($row['user_id']);
        }
    }

    /**
     * @throws RandomException
     */
    private function generateSecureAccessToken($length = 64): string
    {
        $bytes = random_bytes($length / 2);
        return bin2hex($bytes);
    }

}