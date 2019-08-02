<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements \Countable {

	use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['id'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	protected $dates = ['deleted_at'];

	private $count = 0;

    public function roles()
    {
        return $this->belongsToMany(Role::class);
	}

    public function hasRole($role)
    {
        if (is_string($role)){
            return $this->roles->contains('name', $role);
        }

        return !! $role->intersect($this->roles)->count();
	}

    /**
     * Get the user's first name
     *
     * @param  string  $value
     * @return string
     */
    public function getFNameAttribute($value)
    {
        if (!empty($value)) {
            return $this->getDecryptedData($value);
        }

        return null;
	}

    /**
     * Get the user's last name
     *
     * @param  string  $value
     * @return string
     */
	public function getLNameAttribute($value)
	{
        if (!empty($value)) {
            return $this->getDecryptedData($value);
        }

        return null;
	}

    /**
     * Get the user's email.
     *
     * @param  string  $value
     * @return string
     */
	public function getEmailAttribute($value)
	{
        if (!empty($value)) {
            return $this->mayaDecrypt($value);
        }
        return null;
	}

    /**
     * Get the user's phone.
     *
     * @param  string  $value
     * @return string
     */
    public function getPhoneAttribute($value)
    {
        if (!empty($value)) {
            return $this->mayaDecrypt($value);
        }

        return null;
    }

    /**
     * Get the user's first name
     *
     * @param  string  $value
     */
    public function setFNameAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['f_name'] = $this->storeEncryptedData($value);
        }
    }

    /**
     * Get the user's last name
     *
     * @param  string  $value
     */
    public function setLNameAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['l_name'] = $this->storeEncryptedData($value);
        }
    }

    /**
     * Get the user's email.
     *
     * @param  string  $value
     */
    public function setEmailAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['email'] = $this->mayaEncrypt($value);
        }
    }

    public function isUser()
    {
        return $this->type == 'user';
    }

    public function isAdmin()
    {
        return $this->type == 'admin';
    }

    public function isExpert()
    {
        return $this->type == 'specialist';
    }

    public function isFTE()
    {
        $expert_id = optional($this->specialistProfile)->specialist_id;


        if (optional($this->specialistProfile)->job_type == 'FTE' || in_array($expert_id, Miscellaneous::$special_ode)){
            return true;
        }

        return false;
    }

    public function isPTE()
    {
        if (optional($this->specialistProfile)->job_type == 'PTE'){
            return true;
        }

        return false;
    }

    public function isODE()
    {
        if (optional($this->specialistProfile)->job_type == 'ODE'){
            return true;
        }

        return false;
    }

    /**
     * Get the user's phone.
     *
     * @param  string  $value
     */
    public function setPhoneAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['phone'] = $this->mayaEncrypt($value);
        }
    }

    public function getDecryptedData($value)
    {
        $newEncrypter = new Encrypter(\Config::get('config.E_KEY'), \Config::get('app.cipher'));
        return $newEncrypter->decrypt($value);
    }

    public function storeEncryptedData($value)
    {
        $newEncrypter = new Encrypter(\Config::get('config.E_KEY'), \Config::get('app.cipher'));
        return $newEncrypter->encrypt($value);
    }

    public function mayaEncrypt($data)
    {
        $encryption_key = base64_decode(\Config::get('config.E_KEY'));
        $iv = 1245891314192026;
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public function mayaDecrypt($data)
    {
        $encryption_key = base64_decode(\Config::get('config.E_KEY'));
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'AES-256-CBC', $encryption_key, 0, $iv);
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function isPremium()
    {
        return $this->is_premium == 1;
    }

	function TagsFollowed()
	{
		return $this->belongsToMany('Tag', 'following_tags', 'user_id', 'tag_id');
	}

	function QuestionsFollowed()
	{
		return $this->belongsToMany('Question', 'following_questions', 'user_id', 'question_id');
	}

	function NotificationSettings()
	{
		return $this->hasOne('NotificationSetting', 'user_id');
	}

    function notifications()
	{
		return $this->hasMany(Notification::class, 'notifiable')->with('message');
	}

	function Questions()
	{
		return $this->hasMany('Question', 'specialist_id');
	}

    function OwnQuestions()
    {
        return $this->hasMany(Question::class, 'user_id')->orderBy('created_at', 'desc');
    }


	function Stretches()
	{
		return $this->hasMany('Stretch');
	}

	function Answers()
	{
		return $this->hasMany(Answer::class, 'user_id');
	}

	function Comments()
	{
		return $this->hasMany('Comment', 'user_id');
	}

	function Likes()
	{
		return $this->hasMany('Like', 'user_id');
	}

	public function Schedules()
	{
		return $this->belongsToMany('Interval', 'intervals_specialists', 'specialist_id');
	}

	public function Ratings(){
		return $this->hasMany(Rating::class);
	}

	public function setBirthdayAttribute($date){
	    if(isset($date) && !empty($date)){
            $this->attributes['birthday'] = Carbon::parse($date)->format('Y-m-d');
        }
	}

	public function getBirthdayAttribute($value){
	    if (!empty($value) && $value != '0000-00-00 00:00:00' && $value != 0) {
            return Carbon::parse($value)->toFormattedDateString();
        }

        return null;
	}

    /**
     * Formatted created at
     *
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->toFormattedDateString();
    }

	/**
     * Formatted updated at
     *
     * @param $value
     * @return string
     */
    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->toFormattedDateString();
    }

    /**
     * Fetch Specialist's response time
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function responseTime()
	{
		return $this->hasMany('App\ResponseTime');
	}

    /**
     * Fetch specialist profile
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function specialistProfile()
    {
        return $this->hasOne(SpecialistProfile::class, 'specialist_id');
    }

    /**
     * Relationship with subscribe premium table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
	public function subscriberPremium()
	{
		return $this->hasOne('App\SubscriberPremium');
	}

    /**
     * Get answered by specialist id
     *
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeAnsweredBySpecialist($query, $id)
    {
        return $query->whereUserId($id);
    }

    /**
     * Fetch Specialist's Notifications
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function specialistNotifications()
    {
        return $this->hasMany(NotificationSpecialists::class, 'notifiable');
    }

    /**
     * Relationship with push subscription model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pushSubscription()
    {
        return $this->hasOne(PushSubscription::class);
    }

    /**
     * Relationship with user's block table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function block()
    {
        return $this->hasOne(Block::class, 'user_id');
    }

	/**
	 * Creates models from the raw results (it does not check the fillable attributes and so on)
     *
	 * @param array $rawResult
	 * @return Collection
	 */
	public static function modelsFromRawResults($rawResult = [])
	{
		$objects = [];
		foreach($rawResult as $result) {
			$object = new static();

			$object->setRawAttributes((array)$result, true);

			$objects[] = $object;
		}

		return new Collection($objects);
	}

    public function expertGroup()
    {
        return $this->hasOne(ExpertGroup::class, 'expert_id');
    }

    public function answerHistory()
    {
        return $this->hasMany(AnswerHistory::class);
    }

    public function audit()
    {
        return $this->hasMany(Audit::class, 'audited_by');
    }

    public function profilePicture()
    {
        return $this->hasOne(ProfilePicture::class, 'expert_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}

