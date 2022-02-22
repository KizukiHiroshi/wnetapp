<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;

class AccountuserRequest extends FormRequest
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
            'user_id' => ['required','integer','numeric',],
            'name' => ['required','string','max:12',],
            'password' => ['required','string','max:16','regex:/^[a-zA-Z0-9-_]+$/'],
            'company_id' => ['required','integer','numeric',],
            'department_id' => ['required','integer','numeric',],
            'businessunit_id' => ['required','integer','numeric',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
