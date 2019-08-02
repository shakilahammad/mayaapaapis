<?php

namespace App\Providers;

use App\Events\Subscribed;
use App\Listeners\AnswerPost;
use App\Events\AnswerWasPost;
use App\Events\QuestionWasCreated;
use App\Listeners\SubscribedListener;
use App\Listeners\Question\PremiumWork;
use App\Listeners\Question\RealTimeUpdate;
use App\Listeners\Question\AutomationWork;
use App\Events\Literacy\SendLiteracyNotification;
use App\Listeners\Literacy\SendLiteracyNotificationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AnswerWasPost::class => [
            AnswerPost::class
        ],

        QuestionWasCreated::class => [
            AutomationWork::class,
            PremiumWork::class,
            RealTimeUpdate::class
        ],

        Subscribed::class => [
            SubscribedListener::class
        ],

        SendLiteracyNotification::class => [
            SendLiteracyNotificationListener::class
        ],

        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\Auth\LogSuccessfulLogin',
        ],

        'Illuminate\Auth\Events\Logout' => [
            'App\Listeners\Auth\LogSuccessfulLogout',
        ],

        'App\Events\AnswerShouldBeReply' => [
            'App\Listeners\AnswerShouldBeReplyListener'
        ],

        'App\Events\CreatePointTransaction' => [
            'App\Listeners\PointTransactionCreated'
        ]

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
