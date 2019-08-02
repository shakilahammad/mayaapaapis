<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumFeature extends Model implements \Countable
{
    protected $table = 'premium_feature_list';

    protected $guarded = ['id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
