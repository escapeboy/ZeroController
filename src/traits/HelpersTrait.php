<?php

namespace ZeroController\Traits;

use Illuminate\Http\Request;

/**
 * Trait HelpersTrait
 * @package ZeroController\Traits
 */
trait HelpersTrait
{
    /**
     * @param Request $request
     */
    public function autoFill(Request $request)
    {
        $this->fill(array_filter($request->only($this->getFillable()), function ($key) use ($request) {
            return in_array($key, array_keys($request->all())) || @$this->getCasts()[$key] == 'boolean';
        }, ARRAY_FILTER_USE_KEY))
             ->save();
        return $this;
    }
    
    public function reflection()
    {
        if (!is_null($this)) {
            $class = get_class($this);
            if (class_exists($class)) {
                return new \ReflectionClass($class);
            }
        }
    }
    
    public function getRelationships()
    {
        if (!$this->relationships) {
            return [];
        }

        return $this->relationships;
    }
}
