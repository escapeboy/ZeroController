<?php

namespace ZeroController\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ZeroController\Interfaces\Request;

class ZeroRequest extends FormRequest implements Request
{

    public $user = null;

    public function __construct(\Illuminate\Http\Request $request) {

        parent::__construct(
                $request->query->all(),
            (array)$request->post,
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->content
        );
        $this->user = request()->user();
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        if (!$this->user) {
            return false;
        }
        return $this->{'can' . ucfirst($this->method())}();
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function putRules(): array
    {
        return $this->postRules();
    }

    /**
     * @return array
     */
    public function postRules(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function deleteRules(): array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function canPost(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canGet(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canPut(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canDelete(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->{$this->method() . 'Rules'}();
    }
}