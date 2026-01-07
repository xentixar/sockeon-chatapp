<?php

namespace App\Repositories;

use App\Utils\Config;
use PDO;

class MessageRepository extends BaseRepository
{
    public function getMessages(int $userId): array
    {
        $key = Config::env('APP_KEY');
        $statement = $this->database->query('SELECT messages.message, messages.created_at as timestamps, users.name as username, messages.user_id FROM messages JOIN users on (messages.user_id = users.id)');
        $messages = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($messages as &$message) {
            $message['message'] =  openssl_decrypt($message['message'], 'aes-256-cbc', $key);
            $message['is_me'] = ($message['user_id'] == $userId);
        }

        return $messages;
    }

    public function storeMessage(string $message, int $userId): bool
    {
        $key = Config::env('APP_KEY');
        $encrypted = openssl_encrypt($message, 'aes-256-cbc', $key);
        $statement = $this->database->prepare('INSERT INTO messages (message, user_id) VALUES (:message, :user_id)');
        $statement->bindValue(':message', $encrypted);
        $statement->bindValue(':user_id', $userId);
        return $statement->execute();
    }
}