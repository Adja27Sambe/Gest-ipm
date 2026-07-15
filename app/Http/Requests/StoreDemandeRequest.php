<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La Policy s'en charge généralement, mais pour simplifier ici :
        return true;
    }

    public function rules(): array
    {
        return [
            'motif' => 'required|string|max:255',
            'id_type_demande' => 'required|integer|exists:type_demande,id_type_demande',
            'id_salarie' => 'required|integer|exists:salarie,id_salarie',
            'id_ayant_droit' => 'nullable|integer|exists:ayant_droit,id_ayant_droit',
            'auto_approuver' => 'nullable|boolean',
        ];
    }
    
    public function messages(): array
    {
        return [
            'motif.required' => 'Le motif de la demande est obligatoire.',
            'id_type_demande.required' => 'Le type de demande est requis.',
            'id_salarie.required' => 'Veuillez lier cette demande à un salarié.',
        ];
    }
}
