<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PremiumCoupon extends Model implements \Countable
{
    use SoftDeletes;

    protected $table = 'premium_coupons';

    protected $guarded = ['id'];

    public function appliedCoupon()
    {
        return $this->hasMany(PremiumCouponApplied::class, 'coupon_id');
    }

    public function premiumPayment()
    {
        return $this->hasMany(PremiumPayment::class, 'coupon_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
