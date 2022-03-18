<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobtypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => ['required','string','max:3',
                Rule::unique('jobtypes')->ignore($this->input('id')),'regex:/[0-9]{3}/'],
            'name' => ['required','string','max:20','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_system' => ['required','string','max:40',
                Rule::unique('jobtypes')->ignore($this->input('id')),'regex:/^[a-zA-Z0-9-_]+$/'],
            'remarks' => ['nullable','string','max:80',],
        ];
    }
}
