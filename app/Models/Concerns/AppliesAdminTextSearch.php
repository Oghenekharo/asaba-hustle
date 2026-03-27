<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesAdminTextSearch
{
    protected function applyAdminTextSearch(
        Builder $query,
        string $term,
        array $columns = [],
        array $relations = []
    ): Builder {
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($columns, $relations, $term) {
            $this->applyLikeConditions($inner, $columns, $term);

            foreach ($relations as $relation => $relationColumns) {
                $inner->orWhereHas($relation, function (Builder $related) use ($relationColumns, $term) {
                    $this->applyLikeConditions($related, $relationColumns, $term);
                });
            }
        });
    }

    protected function applyLikeConditions(Builder $query, array $columns, string $term): Builder
    {
        foreach (array_values($columns) as $index => $column) {
            $method = $index === 0 ? 'where' : 'orWhere';
            $query->{$method}($column, 'like', "%{$term}%");
        }

        return $query;
    }
}
