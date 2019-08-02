<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PremiumCouponApplied extends Model implements \Countable
{
    use SoftDeletes;

    protected $table = 'premium_coupon_applied';

    protected $guarded = ['id'];

    public function coupon()
    {
        return $this->belongsTo(PremiumCoupon::class);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
