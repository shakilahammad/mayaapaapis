<?php

namespace App\Listeners\Question;

use App\Models\PremiumPayment;
use App\Events\QuestionWasCreated;

class PremiumWork
{
    public function handle(QuestionWasCreated $event)
    {
        $question = $event->question;
        $payment = PremiumPayment::with(['premiumPackage'])
                    ->whereUserId($question->user_id)
                    ->whereIn('status', ['active','free_premium'])
                    ->whereIn('package_id', [5,7])
                    ->first();

        if ($question->isPremium() && isset($payment) && count($payment)) {
            $payment->increment('question_count');

            if ($payment->premiumPackage->isPrescription()) {
                $question->update([
                    'is_prescription' => 1
                ]);
            }
        }
    }

}
