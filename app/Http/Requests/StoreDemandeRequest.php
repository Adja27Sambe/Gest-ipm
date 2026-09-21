<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TypeDemande;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normaliser type_acte vers choix_acte si présent
        if ($this->has('type_acte') && !$this->has('choix_acte')) {
            $this->merge(['choix_acte' => (array) $this->input('type_acte')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $typeDemandeId = $this->input('id_type_demande');
        $typeDemande = $typeDemandeId ? TypeDemande::find($typeDemandeId) : null;
        $libelle = $typeDemande ? strtolower($typeDemande->libelle) : '';

        $rules = [
            'id_type_demande' => 'required|exists:type_demande,id_type_demande',
            'id_salarie' => 'required|exists:PARTICIPANT,IDPARTICIPANT',
            'id_ayant_droit' => 'nullable|exists:ayant_droit,id_ayant_droit',
            'id_type_prestation' => 'nullable|exists:type_prestation,id_type_prestation',
            'motif' => 'nullable|string|max:1000',
            'date_demande' => 'nullable|date',
            'observations' => 'nullable|string|max:1000',
            'diagnostic' => 'nullable|string|max:1000',
        ];

        if (str_contains($libelle, 'bon')) {
            // RÈGLES BON DE COMMANDE : Pharmacie requise, pas de médecin, ordonnance valide max 6 mois, nb articles >= 1
            $rules['id_pharmacie'] = 'required|exists:PHARMACI,PHCLEUNIK';
            $rules['id_praticien'] = 'nullable|prohibited';
            $rules['date_ordonnance'] = 'required|date|before_or_equal:today|after_or_equal:' . now()->subMonths(6)->toDateString();
            $rules['nombre_articles'] = 'required|integer|min:1';
        } elseif (str_contains($libelle, 'feuille')) {
            // RÈGLES FEUILLE DE MALADIE : Praticien (médecin/clinique) requis, pas de pharmacie
            $rules['id_praticien'] = 'required|exists:PRATICIE,PRCLEUNIK';
            $rules['id_pharmacie'] = 'nullable|prohibited';
        } elseif (str_contains($libelle, 'lettre')) {
            // RÈGLES LETTRE DE GARANTIE : Praticien requis, pas de pharmacie, choix d'acte obligatoire
            $rules['id_praticien'] = 'required|exists:PRATICIE,PRCLEUNIK';
            $rules['id_pharmacie'] = 'nullable|prohibited';
            $rules['choix_acte'] = 'required';
        } else {
            $rules['id_praticien'] = 'nullable|exists:PRATICIE,PRCLEUNIK';
            $rules['id_pharmacie'] = 'nullable|exists:PHARMACI,PHCLEUNIK';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'id_type_demande.required' => 'Le type de demande est requis.',
            'id_type_demande.exists' => 'Le type de demande sélectionné est invalide.',
            'id_salarie.required' => 'Le participant (salarié) est requis.',
            'id_salarie.exists' => 'Le participant sélectionné est introuvable.',
            'id_ayant_droit.exists' => 'L\'ayant droit sélectionné est invalide.',
            
            // Règles Pharmacie / Bon de Commande
            'id_pharmacie.required' => 'Une pharmacie ou opticien conventionné est obligatoire pour un Bon de Commande.',
            'id_pharmacie.exists' => 'La pharmacie sélectionnée est introuvable dans la liste des partenaires.',
            'id_pharmacie.prohibited' => 'Une pharmacie ne peut pas être associée à une consultation médicale ou lettre de garantie.',
            'date_ordonnance.required' => 'La date de prescription de l\'ordonnance est obligatoire.',
            'date_ordonnance.date' => 'La date de prescription doit être une date valide.',
            'date_ordonnance.before_or_equal' => 'La date de l\'ordonnance ne peut pas être ultérieure à aujourd\'hui.',
            'date_ordonnance.after_or_equal' => 'L\'ordonnance est expirée. Elle doit dater de moins de 6 mois.',
            'nombre_articles.required' => 'Le nombre d\'articles prescrits est obligatoire.',
            'nombre_articles.integer' => 'Le nombre d\'articles doit être un nombre entier.',
            'nombre_articles.min' => 'Le nombre d\'articles doit être d\'au moins 1.',

            // Règles Praticien / Consultation / Garantie
            'id_praticien.required' => 'Un praticien (médecin ou établissement de soins) est obligatoire pour cette prise en charge.',
            'id_praticien.exists' => 'Le praticien sélectionné est introuvable dans la liste des conventionnés.',
            'id_praticien.prohibited' => 'Un médecin ne peut pas être sélectionné pour un Bon de Commande (sélectionnez une pharmacie).',
            'choix_acte.required' => 'Veuillez sélectionner au moins un acte médical pour la Lettre de Garantie (ex: Hospitalisation, Radiologie, etc.).',
        ];
    }
}
