<?php

namespace App\Models;

use App\Models\ShopProduct;
use Illuminate\Database\Eloquent\Model;
use LaravelFillableRelations\Eloquent\Concerns\HasFillableRelations;

class ShopProductVariant extends Model
{   
    use HasFillableRelations;
    protected $fillable = [
        'shop_product_id',
        'variantId',
        'status',
        'user_id',
        'productId',
        'sku'
    ];
    protected $fillable_relations = ['product'];

    public function product(){
        return $this->belongsTo(ShopProduct::class,'shop_product_id','id');
    }
}