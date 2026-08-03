<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $idUtilisateur = $this->route('utilisateur') ? $this->route('utilisateur')->id_utilisateur : null;

        return [
            'nom' => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'login' => 'required|string|max:50|unique:utilisateur,login,' . $idUtilisateur . ',id_utilisateur',
            'email' => 'nullable|email|max:100|unique:utilisateur,email,' . $idUtilisateur . ',id_utilisateur',
            'mot_de_passe' => 'nullable|string|min:6',
            'id_role' => 'required|exists:role,id_role',
            'statut' => 'required|string|in:actif,inactif',
        ];
    }
}
