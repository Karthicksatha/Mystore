<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataModel extends Model
{
    use HasFactory;
    protected $table = 'mystore';

    protected $fillable = [
        'Date',
        'SKU',
        'Unit Price',
        'Quantity',
        'Total Price',
    ];
}
