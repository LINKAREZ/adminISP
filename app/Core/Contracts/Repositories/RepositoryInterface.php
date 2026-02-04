<?php

namespace App\Core\Contracts\Repositories;

interface RepositoryInterface
{
    public function all(array $columns = ['*']);
    public function find(int $id, array $columns = ['*']);
    public function findOrFail(int $id, array $columns = ['*']);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function paginate(int $perPage = 15, array $columns = ['*']);
}
