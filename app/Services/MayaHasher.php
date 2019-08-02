<?php

namespace App\Services;

use Illuminate\Contracts\Hashing\Hasher;

class MayaHasher implements Hasher {

    private $salt = 'asdfasfdasf';

    public function info($hashedValue){

    }

    public function make($value, array $options = array())
    {
        $encrypted = sha1($this->salt.$value);
        return $encrypted;
    }

    public function check($value, $hashedValue, array $options = array())
    {
        $encrypted = sha1($this->salt.$value);
        return $encrypted == $hashedValue;
    }

    public function needsRehash($hashedValue, array $options = array())
    {

    }

}
