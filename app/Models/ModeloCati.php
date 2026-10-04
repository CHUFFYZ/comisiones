<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class ModeloCati extends Model
{
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $guarded = [];
}