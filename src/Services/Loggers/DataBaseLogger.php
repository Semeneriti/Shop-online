<?php
namespace Services\Loggers;

use PDO;

class DatabaseLogger implements LoggerInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function error(string $message, array $context = []): void
    {
        $this->saveToDb('ERROR', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->saveToDb('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->saveToDb('WARNING', $message, $context);
    }

    private function saveToDb(string $level, string $message, array $context = []): void
    {
        $sql = "INSERT INTO logs (level, message, context, created_at) 
                VALUES (:level, :message, :context, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':level' => $level,
            ':message' => $message,
            ':context' => json_encode($context)
        ]);
    }
}