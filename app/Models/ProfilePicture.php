<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePicture extends Model implements \Countable
{
    protected $table = 'profile_picture';

    protected $guarded = ['id'];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return $this->attributes['url'] = 'https://images-maya.s3.ap-southeast-1.amazonaws.com/images/userprofile/'. $this->attributes['endpoint'];
    }

    public function compressImage($source, $destination, $quality) {
        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source);

        elseif ($info['mime'] == 'image/gif')
            $image = imagecreatefromgif($source);

        elseif ($info['mime'] == 'image/png')
            $image = imagecreatefrompng($source);

        imagepng($image, $destination, $quality);
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }

}
