<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $rules= [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'role' => 'required|in:Admin,Supervisor,Agent',
            'email' => 'required|email|unique:users,email',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'date_of_birth' => 'required|date',
            'timezone' => 'required|string|max:255',
        ];
        if ($this->isMethod('put')) {
            $userId = $this->route('id');
            $rules['email'] = 'required|email|unique:users,email,' . $userId;
        }

        return $rules;
    }
}