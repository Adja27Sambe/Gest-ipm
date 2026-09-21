<?php

namespace App\Traits;

/**
 * Trait MapsIntersecColumns
 * 
 * Provides transparent column name mapping between Laravel-style snake_case
 * attribute names used in views/controllers and the actual INTERSEC column names.
 * 
 * Models define a $columnMap array: ['laravel_name' => 'INTERSEC_COLUMN_NAME']
 */
trait MapsIntersecColumns
{
    /**
     * Set the connection to default if not set.
     */
    public function initializeMapsIntersecColumns()
    {
        if (empty($this->connection)) {
            $this->setConnection(config('database.default', 'mysql'));
        }
    }

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Database\IntersecEloquentBuilder($query);
    }

    /**
     * Get the column map for this model.
     */
    protected function getColumnMap(): array
    {
        return $this->columnMap ?? [];
    }

    /**
     * Get the reverse map (INTERSEC -> Laravel).
     */
    protected function getReverseColumnMap(): array
    {
        return array_flip($this->getColumnMap());
    }

    /**
     * Translate a Laravel attribute name to the INTERSEC column name.
     */
    public function mapToIntersec(string $key): string
    {
        if ($key === 'id') {
            return $this->getKeyName();
        }

        $map = $this->getColumnMap();
        if (isset($map[$key])) {
            return $map[$key];
        }

        // Fallback for id_xxx (e.g. id_salarie, id_entreprise, id_pharmacie)
        if (str_starts_with($key, 'id_')) {
            $expectedSuffix = strtolower(class_basename($this));
            if ($key === 'id_' . $expectedSuffix) {
                return $this->getKeyName();
            }
            $snakeCaseSuffix = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', class_basename($this)));
            if ($key === 'id_' . $snakeCaseSuffix) {
                return $this->getKeyName();
            }
        }

        return $key;
    }

    /**
     * Translate an INTERSEC column name back to the Laravel attribute name.
     */
    protected function mapFromIntersec(string $key): string
    {
        $reverse = $this->getReverseColumnMap();
        return $reverse[$key] ?? $key;
    }

    /**
     * Override getAttribute to support mapped column names.
     */
    public function getAttribute($key)
    {
        // Intercept 'id' directly to the primary key
        if ($key === 'id') {
            return parent::getAttribute($this->getKeyName());
        }

        $intersecKey = $this->mapToIntersec($key);
        
        // If the key was mapped, get the value from the actual column
        if ($intersecKey !== $key) {
            // Check if there's an accessor defined for the Laravel key
            if ($this->hasGetMutator($key) || $this->hasAttributeGetMutator($key)) {
                return parent::getAttribute($key);
            }
            return parent::getAttribute($intersecKey);
        }
        
        // Fallback for id_xxx (if it's not mapped but looks like the model's primary key ID)
        $value = parent::getAttribute($key);
        if ($value === null && str_starts_with($key, 'id_')) {
            $expectedSuffix = strtolower(class_basename($this));
            // e.g. 'id_entreprise' for Entreprise, 'id_salarie' for Salarie
            // If the requested property matches this pattern, return the primary key
            if ($key === 'id_' . $expectedSuffix) {
                return parent::getAttribute($this->getKeyName());
            }
            // Some names don't match exactly, like $facture->id_facture (Facture matches)
            // But $ad->id_ayant_droit (AyantDroit matches if we convert snake_case)
            $snakeCaseSuffix = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', class_basename($this)));
            if ($key === 'id_' . $snakeCaseSuffix) {
                return parent::getAttribute($this->getKeyName());
            }
        }
        
        return $value;
    }

    /**
     * Override setAttribute to support mapped column names.
     */
    public function setAttribute($key, $value)
    {
        // Intercept 'id' directly to the primary key
        if ($key === 'id') {
            return parent::setAttribute($this->getKeyName(), $value);
        }

        $intersecKey = $this->mapToIntersec($key);
        
        if ($intersecKey !== $key) {
            if ($this->hasSetMutator($key) || $this->hasAttributeSetMutator($key)) {
                return parent::setAttribute($key, $value);
            }
            return parent::setAttribute($intersecKey, $value);
        }

        // Fallback for id_xxx
        if (str_starts_with($key, 'id_')) {
            $expectedSuffix = strtolower(class_basename($this));
            if ($key === 'id_' . $expectedSuffix) {
                return parent::setAttribute($this->getKeyName(), $value);
            }
            $snakeCaseSuffix = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', class_basename($this)));
            if ($key === 'id_' . $snakeCaseSuffix) {
                return parent::setAttribute($this->getKeyName(), $value);
            }
        }
        
        return parent::setAttribute($key, $value);
    }

    /**
     * Override toArray to use Laravel attribute names in output.
     */
    public function toArray()
    {
        $array = parent::toArray();
        $reverse = $this->getReverseColumnMap();
        $mapped = [];
        
        foreach ($array as $key => $value) {
            $laravelKey = $reverse[$key] ?? $key;
            $mapped[$laravelKey] = $value;
        }
        
        return $mapped;
    }

    /**
     * Override getFillable to work with mapped column names.
     */
    public function isFillable($key)
    {
        $intersecKey = $this->mapToIntersec($key);
        return parent::isFillable($key) || parent::isFillable($intersecKey);
    }
}
