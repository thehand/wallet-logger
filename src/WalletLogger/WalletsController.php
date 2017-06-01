<?php

namespace WalletLogger;

use Illuminate\Database\Query\Builder;

class WalletsController extends ItemsController
{
    public function __construct(Builder $table)
    {
        parent::__construct($table);
        $this->order_by = 'name';
    }
}