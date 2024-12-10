<?php

namespace BrandonJBegle\CachedRepositories;

use BrandonJBegle\RepositoryException;
use BrandonJBegle\Contracts\RepositoryInterface;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;

/*
 * Abstract Eloquent Repository class
 */

abstract class AbstractEloquentRepository implements RepositoryInterface
{
    /*
     * @var App
     */
    private $app;

    /*
     * @var Eloquent model
     */
    protected $model;

    /*
     * @param App $app
     * @throws App\Repositories\Exceptions\RepositoryException
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model Class name
     *
     * @return mixed
     */
    abstract protected function model();

    /*
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /*
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /*
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /*
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        if ($attribute == 'id') {
            return $this->model->find($id)->update($data);
        } else {
            $updated = $this->model->where($attribute, '=', $id)->update($data);
            if ($updated) {
                return $updated;
            }
        }
    }

    /*
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    /*
     * @param $id
     * @return mixed
     */
    public function restore($id)
    {
        return $this->model->withTrashed()->find($id)->restore();
    }

    /*
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /*
     * @param $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($field, $value, $columns = ['*'])
    {
        return $this->model->where($field, '=', $value)->first($columns);
    }

    public function with(array $relations)
    {
        return $this->model->with($relations);
    }

    /*
     * Model factory
     *
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (! $model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    public function observe($observer)
    {
        return $this->model->observe($observer);
    }
}
