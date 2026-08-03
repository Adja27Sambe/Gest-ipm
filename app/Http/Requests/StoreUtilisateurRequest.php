<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'login' => 'required|string|max:50|unique:utilisateur,login',
            'email' => 'nullable|email|max:100|unique:utilisateur,email',
            'mot_de_passe' => 'required|string|min:6',
            'id_role' => 'required|exists:role,id_role',
            'statut' => 'required|string|in:actif,inactif',
        ];
    }
}
