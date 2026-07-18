<?php

namespace App\Enums;

enum DevisStatut: string
{
    case SOUMIS = 'soumis';
    case EN_REVUE = 'en_revue';
    case VALIDE = 'valide';
    case REJETE = 'rejete';
}
