<?php

namespace ZeroController\Services;

class CallableItem
{
    public $request;

    function __construct()
    {
        $this->request = request();
    }

    /**
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    public function reflection()
    {
        if (!is_null($this)) {
            $class = get_class($this);
            if (class_exists($class)) {
                return new \ReflectionClass($class);
            }
        }
    }
}