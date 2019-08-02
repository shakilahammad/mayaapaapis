<?php

namespace App\Providers;

use App\Models\Invite;
use App\Models\Like;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\LockQueue;
use App\Models\PremiumCouponApplied;
use App\Models\QuizUser;
use App\Models\Refer;
use App\Models\Reply;
use App\Models\Spam;
use App\Observers\AnswerObserver;
use App\Observers\CommentObserver;
use App\Observers\InviteObserver;
use App\Observers\LikeObserver;
use App\Observers\LockedQueueObserver;
use App\Observers\PremiumCouponAppliedObserver;
use App\Observers\QuizUserObserver;
use App\Observers\ReplyObserver;
use App\Observers\SpamObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Answer::observe(AnswerObserver::class);

        Comment::observe(CommentObserver::class);

        Reply::observe(ReplyObserver::class);

        Like::observe(LikeObserver::class);

        LockQueue::observe(LockedQueueObserver::class);

        Spam::observe(SpamObserver::class);

        Invite::observe(InviteObserver::class);

        QuizUser::observe(QuizUserObserver::class);

        PremiumCouponApplied::observe(PremiumCouponAppliedObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'App\Services\Registrar'
        );

        if($this->app->isLocal()){
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }
}
