<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbsUser extends Model implements \Countable
{
    protected $table = 'fbs_users';

    protected $fillable = ['name', 'mobile', 'fbs_training_id', 'created_at'];

    public function fbsTraining()
    {
        $this->belongsTo(FbsTraining::class);
    }

    public function setMobileAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['mobile'] = $this->mayaEncrypt($value);
        }
    }

    public function mayaEncrypt($data)
    {
        $encryption_key = base64_decode(\Config::get('config.E_KEY'));
        $iv = 1245891314192026;
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
