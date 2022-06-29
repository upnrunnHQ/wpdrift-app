<?php
// Discounts(EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Discount extends Eloquent
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'ID',
        'post_author',
        'post_date',
        'post_content',
        'post_title',
        'post_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_content_filtered',
        'post_parent',
        'guid',
        'menu_order',
        'comment_count',
    ];
    /**
    * For adding discount metas relation with discounts
    */
    public function discountsmetas()
    {
        return $this->hasMany('App\DiscountMeta', ['store_id', 'ID'] , ['store_id', 'post_id']);
    }
}
