<?php

namespace App\Observers;

use App\Classes\Miscellaneous;

class LockedQueueObserver
{
    public function deleted()
    {
        $this->call();
    }

    public function saved()
    {
        $this->call();
    }

    private function call()
    {
        Miscellaneous::realTimeUpdateForLockedQuestion();
    }

}
