<?php
namespace ZeroController\Interfaces;


use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;

interface Response
{
    public function __construct($data = []);

    /**
     * @param string $view
     * @return mixed
     */
    public function view(string $view);

    /**
     * @return JsonResponse
     */
    public function json() : JsonResponse;

    /**
     * @return Response
     */
    public function transform() : Response;

    /**
     * @return Redirector
     */
    public function redirect() : Redirector;
}