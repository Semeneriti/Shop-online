<?php
// /var/www/html/src/Request/Request.php
namespace Request;

abstract class Request
{
    protected array $data;
    protected array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->validate();
    }

    abstract protected function validate(): void;

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function all(): array
    {
        return $this->data;
    }

    protected function getValue(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    protected function getInt(string $key, int $default = 0): int
    {
        return (int)($this->data[$key] ?? $default);
    }

    protected function getString(string $key, string $default = ''): string
    {
        return trim($this->data[$key] ?? $default);
    }
}