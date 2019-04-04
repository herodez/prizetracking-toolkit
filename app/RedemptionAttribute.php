<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RedemptionAttribute extends Model
{
    public const NAME_CLAIM_CODE = 'claimcode';
    protected $table = 'redemption_attributes';
    public $timestamps = false;
}
