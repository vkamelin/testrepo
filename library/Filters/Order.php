<?php

namespace App\Filters;

class Order
{
    public function filter($value)
    {
        if ($value == 'desc') {
            return $value;
        } else {
            return 'asc';
        }
    }
}
