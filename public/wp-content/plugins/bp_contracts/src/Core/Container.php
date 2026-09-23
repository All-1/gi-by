<?php

namespace PersonalAccount\Core;

class Container
{
    private array $services = [];

    public function set(string $name, mixed $service): void
    {
        $this->services[$name] = $service;
    }

    public function get(string $name): mixed
    {
        $get = &$this->services[$name] ?? null;
        return  $get;
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }
} 