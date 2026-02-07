<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CafeTable extends Model
{
    protected $fillable = ['table_number', 'qr_code'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
