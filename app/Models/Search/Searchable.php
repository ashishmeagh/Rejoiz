<?php

namespace App\Models\Search;

trait Searchable
{
    public static function bootSearchable()
    {
        // This makes it easy to toggle the search feature flag
        // on and off. This is going to prove useful later on
        // when deploy the new search engine to a live app.
        if (config('services.search.enabled')) {
            static::observe(ProductsObserver::class);
        }
    }

  /*  public function getSearchIndex()
    {
        //return $this->getTable();
       
    }*/


    public function getSearchIndex()
    {
        //return $this->getTable();
        $value=env('ELASTIC_INDEX_NAME','products_repeatbuyz');
        return $value;
    }

    public function getSearchType()
    {
        if (property_exists($this, 'useSearchType')) {
            return $this->useSearchType;
        }

        return $this->getTable();
    }

    public function toSearchArray()
    {
        // By having a custom method that transforms the model
        // to a searchable array allows us to customize the
        // data that's going to be searchable per model.
        return $this->toArray();
    }
}