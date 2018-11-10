<?php

namespace ZeroController\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Mixed_;
use ZeroController\Exceptions\ClassNotCallableException;
use ZeroController\Exceptions\ClassNotFoundException;
use ZeroController\Exceptions\MethodNotCallableException;
use ZeroController\Exceptions\MissingMethodException;
use ZeroController\Services\CallableItem;

class CallController extends Controller
{

    /**
     * @param Request $request
     * @param string  $class_name
     * @param string  $method
     * @return Mixed_
     * @throws ClassNotCallableException
     * @throws ClassNotFoundException
     * @throws MethodNotCallableException
     * @throws MissingMethodException
     * @throws \ReflectionException
     */
    public function call(Request $request, string $class_name, string $method): Mixed_
    {
        /** @var CallableItem $class */
        $class  = '\ZeroController\Services\CallableItems\\' . studly_case($class_name);
        $method = camel_case($method);
        if (!class_exists($class)) {
            throw new ClassNotFoundException('Class ' . $class_name . ' not found.');
        }
        $class = new $class;
        if (!$class instanceof CallableItem) {
            throw new ClassNotCallableException('Class ' . $class->reflection()->getName() . ' is not callable');
        }
        if (!$class->reflection()->hasMethod($method)) {
            throw new MissingMethodException('Class ' . $class->reflection()->getName() . ' don\'t have method ' . $method);
        }
        if (!$class->reflection()->getMethod($method)->isPublic()) {
            throw new MethodNotCallableException('Method ' . $method . ' of ' . $class->reflection()->getName() . ' is not callable');
        }

        return call_user_func_array([$class, $method], $request->all());
    }
}