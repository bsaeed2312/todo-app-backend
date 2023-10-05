<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            "title" => "required|unique:todos",
            "description" => "required",
        ];

        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $todo = $this->route()->parameter('todo');

            $rules['title'] = ['required', Rule::unique('todos')->ignore($todo)];
        }

        return $rules;
    }
}
