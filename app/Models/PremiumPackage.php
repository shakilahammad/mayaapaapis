<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumPackage extends Model implements \Countable
{
    protected $table = 'premium_packages';

    protected $guarded = ['id'];

    public function premiumPayment()
    {
        return $this->hasMany(PremiumPayment::class);
    }

    public function isPrescription()
    {
        return $this->type === 'prescription';
    }

    public function scopeNotPrescription($query)
    {
        return $query->where('type', '!=', 'prescription');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
