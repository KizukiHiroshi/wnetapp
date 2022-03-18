<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountauthorityRequest extends FormRequest
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
            'accountuser_id' => ['required','integer','numeric',
                Rule::unique('accountauthorities')->ignore($this->input('id')),],
            'jobtype_id' => ['required','integer','numeric',
                Rule::unique('accountauthorities')->ignore($this->input('id'))->where(function($query) {
                $query->where('accountuser_id', $this->input('accountuser_id'));
            }),],
            'level' => ['required','integer','numeric',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
