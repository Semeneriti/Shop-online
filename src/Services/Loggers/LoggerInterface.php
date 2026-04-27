<?php

declare(strict_types=1);
// пустая строка вместо, убрать, должна быть одна

namespace Services\Loggers;

interface LoggerInterface
{
    public function error(string $message, array $context = []): void;
    public function info(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
}