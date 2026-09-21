<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrenchLocalizationTest extends TestCase
{
    public function test_application_locale_is_french(): void
    {
        $this->assertEquals('fr', app()->getLocale());
    }

    public function test_validation_messages_are_translated_in_french(): void
    {
        $requiredMsg = __('validation.required', ['attribute' => 'email']);
        $this->assertEquals('Le champ email est obligatoire.', $requiredMsg);

        $numericMsg = __('validation.numeric', ['attribute' => 'montant']);
        $this->assertEquals('Le champ montant doit être un nombre.', $numericMsg);
    }

    public function test_auth_and_passwords_messages_are_translated_in_french(): void
    {
        $this->assertEquals('Ces identifiants ne correspondent pas à nos enregistrements.', __('auth.failed'));
        $this->assertEquals('Votre mot de passe a été réinitialisé avec succès !', __('passwords.reset'));
    }
}
