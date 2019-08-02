<?php

namespace App\Observers;


use App\Events\CreatePointTransaction;
use App\Models\QuizUser;
use Illuminate\Support\Facades\Log;

class QuizUserObserver
{
    /**
     * Handle the quiz user "created" event.
     *
     * @param  \App\QuizUser  $quizUser
     * @return void
     */
    public function created(QuizUser $quizUser)
    {
        if(isset($quizUser->user_id)){
            event(new CreatePointTransaction($quizUser->user_id, 6));
        }

    }

    /**
     * Handle the quiz user "updated" event.
     *
     * @param  \App\QuizUser  $quizUser
     * @return void
     */
    public function updated(QuizUser $quizUser)
    {
        //
    }

    /**
     * Handle the quiz user "deleted" event.
     *
     * @param  \App\QuizUser  $quizUser
     * @return void
     */
    public function deleted(QuizUser $quizUser)
    {
        //
    }

    /**
     * Handle the quiz user "restored" event.
     *
     * @param  \App\QuizUser  $quizUser
     * @return void
     */
    public function restored(QuizUser $quizUser)
    {
        //
    }

    /**
     * Handle the quiz user "force deleted" event.
     *
     * @param  \App\QuizUser  $quizUser
     * @return void
     */
    public function forceDeleted(QuizUser $quizUser)
    {
        //
    }
}
