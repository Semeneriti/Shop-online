<?php

declare(strict_types=1);

namespace Services\Loggers;

class LoggerService implements LoggerInterface
{
    private string $logFile;

    public function __construct(?string $logFile = null)
    {
        if ($logFile === null) {
            $this->logFile = '/var/www/html/src/Storage/Log/errors.txt';
        } else {
            $this->logFile = $logFile;
        }
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $logMessage = '[' . $date . '] [' . $level . '] ' . $message;

        if (!empty($context)) {
            $logMessage .= ' | ';
            foreach ($context as $key => $value) {
                $logMessage .= $key . ': ' . $value . ', ';
            }
            $logMessage = substr($logMessage, 0, -2);
        }

        $logMessage .= PHP_EOL;

        if (file_put_contents($this->logFile, $logMessage, FILE_APPEND) === false) {
            error_log("LOGGER ERROR: " . trim($logMessage));
        }
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
        if ($file === false) {
            return [];
        }

        $totalLines = count($file);
        $lines = min($lines, $totalLines);
        $start = $totalLines - $lines;
        $result = [];

        for ($i = $start; $i < $totalLines; $i++) {
            $result[] = $file[$i];
        }

        return $result;
    }
}