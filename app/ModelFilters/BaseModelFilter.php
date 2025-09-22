<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BaseModelFilter extends ModelFilter
{
    public function sort($val)
    {
        $columns = Schema::getColumnListing($this->getModel()->getTable());
        $order = $this->input('order', 'desc') === 'desc' ? 'desc' : 'asc';

        if (method_exists($this, $method = 'sortBy'.Str::studly($val))) {
            return $this->$method($order);
        }

        if (in_array($val, $columns)) {
            return $this->orderBy($val, $order);
        }
    }

    protected function filterRangeRelationship($key, $val)
    {
        if (filter_var($val, FILTER_VALIDATE_INT)) {
            $operator = '=';
            $amount = $val;
        } else {
            $val = explode(' ', $val);
            $operator = count($val) === 1 ? 'between' : $val[0];
            $amount = count($val) === 1 ? $val[0] : $val[1];
        }

        if ($operator === 'between') {
            $amount = explode('-', $amount);

            // @phpstan-ignore argument.type
            return $this->has($key, 'BETWEEN', DB::raw("{$amount[0]} and {$amount[1]}"));
        }

        return $this->has($key, $operator, (int) $amount);
    }

    protected function filterRange($key, $val)
    {
        if (filter_var($val, FILTER_VALIDATE_INT)) {
            $operator = '=';
            $amount = $val;
        } else {
            $val = explode(' ', $val);
            $operator = count($val) === 1 ? 'between' : $val[0];
            $amount = count($val) === 1 ? $val[0] : $val[1];
        }

        if ($operator === 'between') {
            $amount = explode('-', $amount);

            return $this->whereBetween($key, [(int) $amount[0], (int) $amount[1]]);
            // return $this->where($key, '>', (int) $amount[0])->where($key, '<', (int) $amount[1]);
        }

        return $this->where($key, $operator, (int) $amount);
    }

    protected function filterRangeHaving($agg, $key, $val)
    {
        $keyCol = DB::raw($agg.'('.$key.')');
        if (filter_var($val, FILTER_VALIDATE_INT)) {
            $operator = '=';
            $amount = $val;
        } else {
            $val = explode(' ', $val);
            $operator = count($val) === 1 ? 'between' : $val[0];
            $amount = count($val) === 1 ? $val[0] : $val[1];
        }

        if ($operator === 'between') {
            $amount = explode('-', $amount);

            return $this->having($keyCol, '>', (int) $amount[0])
                ->having($keyCol, '<', (int) $amount[1]);
        }

        return $this->having($keyCol, $operator, (int) $amount);
    }

    protected function filterDate($key, $val)
    {
        if ($val === 'false' || $val === 'null') {
            return $this->where($key, '=', null);
        }

        $val = explode('-', $val);
        $from = date($val[0]);

        if (count($val) === 2) {
            $to = date($val[1]);

            return $this->whereBetween($key, [$from, $to]);
        } else {
            return $this->where($key, '=', $from);
        }
    }

    public function createdAt($val)
    {
        return $this->filterDate('created_at', $val);
    }

    public function updatedAt($val)
    {
        return $this->filterDate('updated_at', $val);
    }
}
