<?php

declare(strict_types=1);

namespace Services\Loggers;

use PDO;

class DataBaseLogger implements LoggerInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function error(string $message, array $context = []): void
    {
        $level = 'ERROR';
        $this->saveToDb($level, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $level = 'INFO';
        $this->saveToDb($level, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $level = 'WARNING';
        $this->saveToDb($level, $message, $context);
    }

    private function saveToDb(string $level, string $message, array $context = []): void
    {

        $contextString = '';
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                $contextString .= $key . ':' . $value . '|';
            }
            $contextString = rtrim($contextString, '|');
        }

        $sql = "INSERT INTO logs (level, message, context, created_at) 
                VALUES (:level, :message, :context, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':level' => $level,
            ':message' => $message,
            ':context' => $contextString
        ]);
    }
}