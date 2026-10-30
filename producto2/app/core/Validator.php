<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    /** @var array<string, string[]> */
    private array $errors = [];

    public function __construct(private array $data) {}

    public function required(string $field, string $label): self 
    {
        if (trim($this->value($field)) === '') {
            $this->add($field, "El campo $label es obligatorio.");
        }

        return $this;
    }

    public function email(string $field, string $label = 'correo'): self 
    {
        $v = trim($this->value($field));
        if ($v !== '' && filter_var($v, FILTER_VALIDATE_EMAIL) === false) {
            $this->add($field, "El $label no tiene un formato válido.");
        }

        return $this;
    }

    public function min(string $field, int $len, string $label): self
    {
        $v = $this->value($field);
        if ($v !== '' && mb_strlen($v) < $len) {
            $this->add($field, "El campo $label debe tener como mínimo $len caracteres.");
        }

        return $this;
    }

    public function max(string $field, int $len, string $label): self
    {
        if (mb_strlen($this->value($field)) > $len) {
            $this->add($field, "El campo $label no puede superar los $len caracteres.");
        }

        return $this;
    }

    public function matches(string $field, string $other, string $msg): self 
    {
        if ($this->value($field) !== $this->value($other)) {
            $this->add($field, $msg);
        }

        return $this;
    }

    public function add(string $field, string $msg): void
    {
        $this->errors[$field][] = $msg;
    }

    public function fails(): bool 
    {
        return $this->errors !== [];
    }

    /** @return array<string, string[]> */
    public function errors(): array
    {
        return $this->errors;
    }

    private function value(string $field): string
    {
        $v = $this->data[$field] ?? '';
        return is_scalar($v) ? (string) $v : '';
    }
}