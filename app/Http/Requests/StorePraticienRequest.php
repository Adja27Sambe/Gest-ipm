<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePraticienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // À sécuriser via Gate si nécessaire
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'specialite' => 'nullable|string|max:255',
        ];
    }
}
