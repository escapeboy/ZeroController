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
     * @return HelpersTrait
     */
    public function autoFill(Request $request): HelpersTrait
    {
        $this->fill(array_filter($request->only($this->getFillable()), function ($key) use ($request) {
            return in_array($key, array_keys($request->all())) || @$this->getCasts()[$key] == 'boolean';
        }, ARRAY_FILTER_USE_KEY))
             ->save();
        return $this;
    }
}