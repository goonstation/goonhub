<?php

namespace App\ModelFilters;

class EventLogFilter extends BaseModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function id($val)
    {
        return $this->where('id', $val);
    }

    public function round($val)
    {
        return $this->where('round_id', $val);
    }

    /**
     * Filter by server_id via the gameRound relationship
     *
     * @param  string  $val
     */
    public function server($val)
    {
        return $this->whereHas('gameRound', function ($q) use ($val) {
            $q->where('server_id', $val);
        });
    }

    /**
     * Filter by log type(s)
     *
     * @param  string|array<string>  $val
     */
    public function type($val)
    {
        if (is_array($val)) {
            return $this->whereIn('type', $val);
        }

        return $this->where('type', $val);
    }

    /**
     * Full-text search using TimescaleDB GIN-indexed tsvector column
     *
     * @param  string  $val
     */
    public function search($val)
    {
        if (empty(trim($val))) {
            return $this;
        }

        return $this->whereRaw(
            "search_vector @@ plainto_tsquery('english', ?)",
            [$val]
        );
    }

    /**
     * Exact phrase search using TimescaleDB GIN-indexed tsvector column
     *
     * @param  string  $val
     */
    public function searchPhrase($val)
    {
        if (empty(trim($val))) {
            return $this;
        }

        return $this->whereRaw(
            "search_vector @@ phraseto_tsquery('english', ?)",
            [$val]
        );
    }
}
