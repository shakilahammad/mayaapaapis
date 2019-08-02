<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicCardActivity extends Model implements \Countable
{
    protected $table = 'dynamic_card_activity';

    protected $guarded = ['id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
