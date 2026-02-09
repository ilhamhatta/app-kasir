<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CafeTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'qr_code',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'cafe_table_id');
    }
}
