<?php
// Discount Metas(EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DiscountMeta extends Eloquent
{
    protected $table = "discounts_metas";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'post_id',
        'meta_key',
        'meta_value'
    ];
    /**
     * Get discounts that have the metas.
     */
    public function discounts()
    {
        return $this->belongsTo('App\Discount');
    }
}
