<?php
// Payment Metas(EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class PaymentMeta extends Eloquent
{
    protected $table = "payments_metas";
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
     * Get payments that have the logs.
     */
    public function payments()
    {
        return $this->belongsTo('App\Payment');
    }
}
