<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'nis' => 'required|string|unique:students,nis',
            'kelas_id' => 'required|exists:kelas,id',
            'parent_id' => 'nullable|exists:users,id',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $student = $this->route('student');
            if ($student && $student->user) {
                $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $student->user->id . ',id';
                $rules['nis'] = 'required|string|unique:students,nis,' . $student->id . ',id';
            }
            $rules['password'] = 'nullable|string|min:8';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter',
            'email.required' => 'Email harus diisi',
            'email.string' => 'Email harus berupa teks',
            'email.email' => 'Email tidak valid',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password harus diisi',
            'password.string' => 'Password harus berupa teks',
            'password.min' => 'Password minimal 8 karakter',
            'nis.required' => 'NIS harus diisi',
            'nis.string' => 'NIS harus berupa teks',
            'nis.unique' => 'NIS sudah digunakan',
            'kelas_id.required' => 'Kelas harus diisi',
            'kelas_id.exists' => 'Kelas tidak ditemukan',
            'parent_id.exists' => 'Orang tua tidak ditemukan',
            'phone.string' => 'Nomor telepon harus berupa teks',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter',
        ];
    }
}
