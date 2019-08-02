<?php

namespace App\Models;

use Carbon\Carbon;
use App\Events\Subscribed;
use Illuminate\Database\Eloquent\Model;

class PremiumPayment extends Model implements \Countable
{
    protected $table = 'premium_payments';

    protected $guarded = ['id'];

    private $count = 0;

    protected $dispatchesEvents = [
        'created' => Subscribed::class//,
//        'updated' => Subscribed::class
    ];

    public function isActive()
    {
        return $this->status == 'active';
    }

    public function getExpiryTimeAttribute($value)
    {
        return Carbon::parse($value)->format('d M Y');
    }

    public function premiumPackage()
    {
        return $this->belongsTo(PremiumPackage::class, 'package_id');
    }

    public function coupon()
    {
        return $this->belongsTo(PremiumCoupon::class, 'coupon_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
