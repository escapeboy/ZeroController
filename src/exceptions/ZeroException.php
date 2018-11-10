<?php

namespace ZeroController\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZeroException extends Exception
{
    public function report()
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function render(Request $request): JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'file'    => $this->getFile() . ' at #' . $this->getLine(),
                'code'    => $this->getCode()
            ]);
        }

        throw new Exception($this->getMessage(), $this->getCode(), $this->getPrevious());
    }
}
