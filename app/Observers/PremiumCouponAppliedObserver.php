<?php

namespace App\Observers;


use App\Events\CreatePointTransaction;
use App\Models\PremiumCouponApplied;

class PremiumCouponAppliedObserver
{
    /**
     * Handle the premium coupon applied "created" event.
     *
     * @param  \App\PremiumCouponApplied  $premiumCouponApplied
     * @return void
     */
    public function created(PremiumCouponApplied $premiumCouponApplied)
    {
        event(new CreatePointTransaction($premiumCouponApplied->user_id, 7));
    }

    /**
     * Handle the premium coupon applied "updated" event.
     *
     * @param  \App\PremiumCouponApplied  $premiumCouponApplied
     * @return void
     */
    public function updated(PremiumCouponApplied $premiumCouponApplied)
    {
        //
    }

    /**
     * Handle the premium coupon applied "deleted" event.
     *
     * @param  \App\PremiumCouponApplied  $premiumCouponApplied
     * @return void
     */
    public function deleted(PremiumCouponApplied $premiumCouponApplied)
    {
        //
    }

    /**
     * Handle the premium coupon applied "restored" event.
     *
     * @param  \App\PremiumCouponApplied  $premiumCouponApplied
     * @return void
     */
    public function restored(PremiumCouponApplied $premiumCouponApplied)
    {
        //
    }

    /**
     * Handle the premium coupon applied "force deleted" event.
     *
     * @param  \App\PremiumCouponApplied  $premiumCouponApplied
     * @return void
     */
    public function forceDeleted(PremiumCouponApplied $premiumCouponApplied)
    {
        //
    }
}
