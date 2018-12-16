<?php namespace ZeroController\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\View;

class ZeroResponse extends Response
{

    public $data = [];

    public function __construct($data = [])
    {
        parent::__construct();
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $view
     * @return View
     */
    public function view(string $view): View
    {
        return view($view, $this->data);
    }

    /**
     * @return JsonResponse
     */
    public function json(): JsonResponse
    {
        return response()->json($this->data);
    }

    /**
     * @return ZeroResponse
     */
    public function transform(): ZeroResponse
    {
        return $this;
    }

    /**
     * @return Redirector
     */
    public function redirect(): Redirector
    {
        return redirect();
    }
}
