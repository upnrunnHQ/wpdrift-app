<?php
// Term Taxonomy (EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TermTaxonomy extends Eloquent
{
    protected $table = "term_taxonomy";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'term_taxonomy_id',
        'term_id',
        'name',
        'slug',
        'taxonomy',
        'description',
        'parent',
        'count'
    ];
}
