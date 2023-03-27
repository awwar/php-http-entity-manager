<?php

namespace Awwar\PhpHttpEntityManager\Client;

interface ClientInterface
{
    public function create(string $path, array $data = []): array;

    public function delete(string $path): void;

    public function get(string $path, array $query = []): array;

    public function update(string $path, array $data = []): array;
}
