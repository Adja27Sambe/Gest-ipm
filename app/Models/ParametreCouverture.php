<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametreCouverture extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'parametre_couverture';
    protected $primaryKey = 'id_parametre';
    protected $guarded = [];

    public function typePrestation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TypePrestation::class, 'id_type_prestation', 'id_type_prestation');
    }
}
