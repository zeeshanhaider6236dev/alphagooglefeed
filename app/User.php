<?php

namespace App;

use App\Models\Setting;
use App\Models\ContactUs;
use App\Models\ShopProduct;
use Osiset\ShopifyApp\Traits\ShopModel;
use Illuminate\Notifications\Notifiable;
use Osiset\ShopifyApp\Storage\Models\Charge;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Osiset\ShopifyApp\Contracts\ShopModel as IShopModel;
use LaravelFillableRelations\Eloquent\Concerns\HasFillableRelations;

class User extends Authenticatable implements IShopModel
{
    use HasFillableRelations;
    use Notifiable;
    use ShopModel;
    protected $fillable_relations = ['products'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','shopify_freemium','plan_id','updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['settings'];

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }
    
    public function shop_contacts()
    {
        return $this->hasMany(ContactUs::class);
    }
    
    public function currentCharge()
    {
        return $this->hasOne(Charge::class)->where('status','ACTIVE');
    }
    
    public function products()
    {
        return $this->hasMany(ShopProduct::class);
    }

}
