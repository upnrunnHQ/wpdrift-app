<?php
// Payments(EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Payment extends Eloquent
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
    * For adding payment metas relation with payments
    */
    public function paymentsmetas()
    {
        return $this->hasMany('App\PaymentMeta', ['store_id', 'ID'] , ['store_id', 'post_id']);
    }
}
