<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motif' => 'sometimes|required|string|max:255',
            'statut' => 'sometimes|required|string|in:En cours,Approuvée,Rejetée',
        ];
    }
}
