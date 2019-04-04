<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Redeemed extends Model
{
    protected $table = 'redeemed';
    public $timestamps = false;
    
    /**
     * Get the user that owns the Redeemed.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
