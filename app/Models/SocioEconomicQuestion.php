<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioEconomicQuestion extends Model implements \Countable
{
    protected $table = 'socio_economic_questions';

    protected $guarded = ['id'];

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
