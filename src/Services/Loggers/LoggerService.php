<?php
// src/Services/LoggerService.php
namespace Services\Loggers;

class LoggerService implements LoggerInterface
{
    private string $logFile;

    public function __construct(string $logFile = null)
    {

        if ($logFile == null) {
            $this->logFile = __DIR__ . '/../Storage/errors.txt';
        } else {
            $this->logFile = $logFile;
        }
    }

    public function error(string $message, array $context = []): void
    {
        $level = 'ERROR';
        $this->log($level, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $level = 'INFO';
        $this->log($level, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $level = 'WARNING';
        $this->log($level, $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        // Текущая дата и время
        $date = date('Y-m-d H:i:s');

        // Начинаем собирать строку для лога
        $logMessage = '[' . $date . '] [' . $level . '] ' . $message;

        // Если есть дополнительные данные
        if (!empty($context)) {
            $logMessage .= ' | ';

            // Перебираем все ключи и значения из массива context
            foreach ($context as $key => $value) {
                $logMessage .= $key . ': ' . $value . ', ';
            }

            // Убираем последние два символа (запятую и пробел)
            $logMessage = substr($logMessage, 0, -2);
        }

        // Добавляем перенос строки
        $logMessage .= PHP_EOL;

        // Записываем в файл
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    public function clear(): void
    {
        file_put_contents($this->logFile, '');
    }

    public function getLastLines(int $lines = 50): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $file = file($this->logFile);
        $totalLines = count($file);

        if ($lines > $totalLines) {
            $lines = $totalLines;
        }

        $start = $totalLines - $lines;
        $result = [];

        for ($i = $start; $i < $totalLines; $i++) {
            $result[] = $file[$i];
        }

        return $result;
    }
}