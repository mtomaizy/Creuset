<?php

namespace Creuset\Repositories;

abstract class DbRepository
{
    protected $model;

    /**
     * @param int   $id
     * @param array $with
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function fetch($id, $with = [])
    {
        return $this->model->with($with)->find($id);
    }

    /**
     * @param array $with
     *
     * @return mixed
     */
    public function getPaginated($with = [])
    {
        return $this->queryAll($with)->paginate(config('shop.products_per_page'));
    }

    /**
     * @param array $with
     *
     * @return mixed
     */
    public function all($with = [])
    {
        return $this->queryAll($with)->get();
    }

    protected function queryAll($with = [])
    {
        return $this->model->with($with)->latest();
    }

    public function getBySlug($slug, $with = [])
    {
        return $this->model->where('slug', $slug)->with($with)->first();
    }
}
