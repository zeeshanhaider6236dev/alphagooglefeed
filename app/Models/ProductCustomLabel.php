<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCustomLabel extends Model
{
    protected $fillable = [
        'user_id','shop_product_id','productId','label'
    ];
}
