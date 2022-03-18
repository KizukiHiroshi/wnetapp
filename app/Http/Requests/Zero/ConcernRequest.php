<?php

namespace App\Http\Requests\Zero;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConcernRequest extends FormRequest
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
            'jobtype_id' => ['required','integer','numeric',],
            'name' => ['required','string','max:40',
                Rule::unique('concerns')->ignore($this->input('id')),],
            'content' => ['required','string','max:255',],
            'importance' => ['required','string','max:2',
                Rule::in(['A', 'B', 'C', 'D', 'E', 'X']),],
            'priority' => ['required','integer','numeric','between:1, 100'],
            'solution' => ['required','string','max:255',],
            'is_solved' => ['required','boolean',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
