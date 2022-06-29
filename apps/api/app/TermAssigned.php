<?php
// Term Assigned (EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TermAssigned extends Eloquent
{
    protected $table = "term_assigned";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'object_id',
        'term_taxonomy_id',
        'term_order'
    ];
}
