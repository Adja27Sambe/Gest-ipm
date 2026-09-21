<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Database\Query\Expression;

class IntersecEloquentBuilder extends Builder
{
    /**
     * Map a column name to its INTERSEC database column name.
     */
    public function mapColumn($column)
    {
        if (!is_string($column) || empty($column)) {
            return $column;
        }

        // If it's a raw expression, don't touch it
        if ($column instanceof Expression) {
            return $column;
        }

        // If table.column format (e.g. 'PARTICIPANT.nom')
        if (str_contains($column, '.')) {
            [$table, $col] = explode('.', $column, 2);
            $mappedCol = method_exists($this->model, 'mapToIntersec')
                ? $this->model->mapToIntersec($col)
                : $col;
            return $table . '.' . $mappedCol;
        }

        return method_exists($this->model, 'mapToIntersec')
            ? $this->model->mapToIntersec($column)
            : $column;
    }

    /**
     * Map an array of columns.
     */
    public function mapColumns($columns)
    {
        if (is_array($columns)) {
            return array_map([$this, 'mapColumn'], $columns);
        }
        return $this->mapColumn($columns);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_string($column)) {
            // Handle 2-arg signature: where('col', 'val')
            if (func_num_args() === 2) {
                $value = $operator;
                $operator = '=';
            }

            $mappedCol = $this->mapColumn($column);

            // Automatic value casting for known legacy enum columns
            if ($mappedCol === 'PARACTIF' || $mappedCol === 'ADACTIF') {
                if (is_string($value)) {
                    $v = strtolower($value);
                    $value = $v === 'actif' ? 1 : ($v === 'suspendu' ? 2 : 0);
                }
            } elseif ($mappedCol === 'SEXE') {
                if (is_string($value)) {
                    $value = strtoupper($value) === 'M' ? 1 : (strtoupper($value) === 'F' ? 2 : $value);
                }
            }

            if (func_num_args() === 2) {
                return parent::where($mappedCol, '=', $value, $boolean);
            }

            return parent::where($mappedCol, $operator, $value, $boolean);
        } elseif (is_array($column)) {
            $mapped = [];
            foreach ($column as $key => $val) {
                $mKey = $this->mapColumn($key);
                if (($mKey === 'PARACTIF' || $mKey === 'ADACTIF') && is_string($val)) {
                    $v = strtolower($val);
                    $val = $v === 'actif' ? 1 : ($v === 'suspendu' ? 2 : 0);
                } elseif ($mKey === 'SEXE' && is_string($val)) {
                    $val = strtoupper($val) === 'M' ? 1 : (strtoupper($val) === 'F' ? 2 : $val);
                }
                $mapped[$mKey] = $val;
            }
            $column = $mapped;
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if (is_string($column)) {
            $column = $this->mapColumn($column);
        }

        return parent::orWhere($column, $operator, $value);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        return parent::whereIn($this->mapColumn($column), $values, $boolean, $not);
    }

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return parent::whereNotIn($this->mapColumn($column), $values, $boolean);
    }

    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        return parent::whereNull($this->mapColumns($columns), $boolean, $not);
    }

    public function whereNotNull($columns, $boolean = 'and')
    {
        return parent::whereNotNull($this->mapColumns($columns), $boolean);
    }

    public function whereBetween($column, iterable $values, $boolean = 'and', $not = false)
    {
        return parent::whereBetween($this->mapColumn($column), $values, $boolean, $not);
    }

    public function whereNotBetween($column, iterable $values, $boolean = 'and')
    {
        return parent::whereNotBetween($this->mapColumn($column), $values, $boolean);
    }

    public function orderBy($column, $direction = 'asc')
    {
        return parent::orderBy($this->mapColumn($column), $direction);
    }

    public function orderByDesc($column)
    {
        return parent::orderByDesc($this->mapColumn($column));
    }

    public function latest($column = null)
    {
        $col = $column ? $this->mapColumn($column) : $this->model->getKeyName();
        return parent::latest($col);
    }

    public function oldest($column = null)
    {
        $col = $column ? $this->mapColumn($column) : $this->model->getKeyName();
        return parent::oldest($col);
    }

    public function select($columns = ['*'])
    {
        $cols = is_array($columns) ? $columns : func_get_args();
        return parent::select($this->mapColumns($cols));
    }

    public function pluck($column, $key = null)
    {
        return parent::pluck(
            $this->mapColumn($column),
            $key ? $this->mapColumn($key) : null
        );
    }
}
