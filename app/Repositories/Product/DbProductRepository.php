<?php

namespace App\Repositories\Product;

use App\Product;
use App\Repositories\DbRepository;
use App\Term;

class DbProductRepository extends DbRepository implements ProductRepository
{
    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    /**
     * @param array $attributes
     *
     * @return static
     */
    public function create($attributes)
    {
        $product = $this->model->create($attributes);
        if (isset($attributes['terms'])) {
            $product->terms()->sync($attributes['terms']);
        }

        return $product;
    }

    public function inCategory(Term $product_category)
    {
        return $product_category->products()->paginate(config('shop.products_per_page'));
    }
}
