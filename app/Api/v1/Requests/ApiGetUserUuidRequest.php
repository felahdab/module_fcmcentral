<?php

namespace Modules\FcmCentral\Api\v1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiGetUserUuidRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "email" => "email:rfc|required_without:nid|missing_with:nid",
            "nid" => "string|required_without:email|missing_with:email"
        ];
    }

    public function messages(): array
{
    return [
        'email.missing_with' => 'you must choose between nid and email',
        'nid.missing_with' => 'you must choose between nid and email',
    ];
}

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
