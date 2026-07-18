<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeRequest extends FormRequest
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
            'id_type_demande' => 'required|exists:type_demande,id_type_demande',
            'id_salarie' => 'required|exists:salarie,id_salarie',
            'id_ayant_droit' => 'nullable|exists:ayant_droit,id_ayant_droit',
            'id_prestataire' => 'nullable|exists:prestataire,id_prestataire',
            'motif' => 'nullable|string|max:1000',
            'date_demande' => 'nullable|date',
            // Pour Feuille de Maladie et Lettre Garantie
            'observations' => 'nullable|string|max:1000',
            // Pour Feuille de Maladie
            'diagnostic' => 'nullable|string|max:1000',
        ];

        if ($this->has('nombre_articles')) {
            $rules['nombre_articles'] = 'integer|min:1';
        }

        if ($this->has('date_ordonnance')) {
            $rules['date_ordonnance'] = 'nullable|date';
        }

        if ($this->has('choix_acte')) {
            $rules['choix_acte'] = 'string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'id_type_demande.required' => 'Le type de demande est requis.',
            'id_salarie.required' => 'Le participant (salarié) est requis.',
            'id_prestataire.exists' => 'Le prestataire sélectionné est invalide.',
        ];
    }
}
