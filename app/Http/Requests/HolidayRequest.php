<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class HolidayRequest extends FormRequest
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
        return [
            'date' => 'required|date|unique:holidays,date,' . $this->route('holiday'),
            'description' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal hari libur wajib diisi',
            'date.date' => 'Tanggal hari libur harus berupa tanggal yang valid',
            'date.unique' => 'Tanggal hari libur sudah ada',
            'description.required' => 'Deskripsi hari libur wajib diisi',
            'description.string' => 'Deskripsi hari libur harus berupa teks',
            'description.max' => 'Deskripsi hari libur tidak boleh lebih dari 255 karakter',
        ];
    }
}
