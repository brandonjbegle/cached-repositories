<?php

namespace BrandonJBegle\CachedRepositories\Contracts;

interface RepositoryInterface
{
    public function all($columns = ['*']);

    public function paginate($perPage = 15, $columns = ['*']);

    public function create(array $data);

    public function update(array $data, $id, $attribute = 'id');

    public function destroy($id);

    public function restore($id);

    public function find($id, $columns = ['*']);

    public function findBy($field, $value, $columns = ['*']);

    public function with(array $relations);
}
