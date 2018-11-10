<?php

namespace ZeroController\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZeroRequest extends FormRequest
{

    public $user = null;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
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