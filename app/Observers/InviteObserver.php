<?php

namespace App\Observers;


use App\Events\CreatePointTransaction;
use App\Models\Invite;
use App\Models\InviteCode;
use Illuminate\Support\Facades\Log;

class InviteObserver
{
    /**
     * Handle the invite "created" event.
     *
     * @param  \App\Invite  $invite
     * @return void
     */
    public function created(Invite $invite)
    {
        $referrer = InviteCode::where('id', $invite->code_id)->first();

//        Log::emergency('invite'. json_encode($invite));
        event(new CreatePointTransaction($invite->recipient_id, 11));
        event(new CreatePointTransaction($referrer->referrer_id, 5));
    }

    /**
     * Handle the invite "updated" event.
     *
     * @param  \App\Invite  $invite
     * @return void
     */
    public function updated(Invite $invite)
    {
        //
    }

    /**
     * Handle the invite "deleted" event.
     *
     * @param  \App\Invite  $invite
     * @return void
     */
    public function deleted(Invite $invite)
    {
        //
    }

    /**
     * Handle the invite "restored" event.
     *
     * @param  \App\Invite  $invite
     * @return void
     */
    public function restored(Invite $invite)
    {
        //
    }

    /**
     * Handle the invite "force deleted" event.
     *
     * @param  \App\Invite  $invite
     * @return void
     */
    public function forceDeleted(Invite $invite)
    {
        //
    }
}
