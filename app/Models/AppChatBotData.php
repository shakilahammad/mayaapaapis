<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppChatBotData extends Model implements \Countable
{
    protected $table = 'app_chat_bot_data';

    protected $guarded = ['id'];
    
    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
