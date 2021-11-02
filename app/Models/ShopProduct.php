<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelFillableRelations\Eloquent\Concerns\HasFillableRelations;

class ShopProduct extends Model
{
    use HasFillableRelations;

    protected $fillable = ['user_id','productId','title','image','status','seoTitle','seoDescription','product_category_id','ageGroup','gender','productCondition'];
    
    protected $fillable_relations = ['variants','labels'];
    
    public function variants()
    {
        return $this->hasMany(ShopProductVariant::class,'shop_product_id','id');
    }

    public function labels(){
        return $this->hasMany(ProductCustomLabel::class,'shop_product_id','id');
    }

    public function category(){
        return $this->belongsTo(ProductCategory::class,'product_category_id','id');
    }
}
