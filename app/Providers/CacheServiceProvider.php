<?php

namespace Creuset\Providers;

use Creuset\Events\ModelWasChanged;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    private $cachable_models = [
        \Creuset\Post::class,
        \Creuset\Product::class,
    ];

    public function boot()
    {
        foreach ($this->cachable_models as $cachable_model) {
            $cachable_model::saved(function($model) {
                $this->fireEvent($model);
            });

            $cachable_model::deleted(function($model) {
                $this->fireEvent($model);
            });

            $cachable_model::restored(function($model) {
                $this->fireEvent($model);
            });
        }
    }

    private function fireEvent($model)
    {
        event(new ModelWasChanged($model->getTable()));
    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
