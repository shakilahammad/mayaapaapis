<?php

namespace App\Models;

use App\Events\QuestionWasCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model implements \Countable
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'questions';

    /**
     * @var array
     */

    protected $guarded = ['id'];

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The event map for the model.
     *
     * @var array
     */
    
    private $count = 0;
    
    protected $dispatchesEvents = [
        'created' => QuestionWasCreated::class
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function Followers()
    {
        return $this->belongsToMany(User::class, 'following_questions', 'question_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function Media()
    {
        return $this->hasMany(Medium::class, 'id', 'media_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function Ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function Answer()
    {
        return $this->hasOne(Answer::class)->orderBy('created_at', 'desc');
    }

    function Prescription()
    {
        return $this->hasOne(Prescription::class)->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function ServiceHolder()
    {
        return $this->belongsTo('PremiumServiceHolder', 'service_holder', 'name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function Comments()
    {
        return $this->hasMany(Comment::class, 'question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function Likes()
    {
        return $this->hasMany(Like::class, 'question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function Specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function Tags()
    {
        return $this->belongsToMany(Tag::class, 'questions_tags', 'question_id')->withTimestamps();
    }

    /**
     * Fetch all tags for a specific question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function tagsLayerTwo()
    {
        return $this->belongsToMany('App\Models\TagLayerTwo','questions_tags_layer_two','question_id', 'tags_layer_two_id')->withTimestamps();
    }

    /**
     * Fetch all tags for a specific question
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function tagsLayerThree()
    {
        return $this->belongsToMany('App\TagLayerThree','questions_tags_layer_three','question_id','tags_layer_three_id')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function Asker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function Notifications()
    {
        return $this->hasMany(Notification::class, 'question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function Referrals()
    {
        return $this->hasMany(Notification::class, 'q_id');
    }

    public function isPremium()
    {
        return $this->is_premium == 1;
    }

    public function isPrescription()
    {
        return $this->is_prescription == 1;
    }

    /**
     * @param $query
     * @param $category_id
     * @param $status
     * @param int $last_id
     * @return mixed
     */
    function scopeWithCategoryAndStatus($query, $category_id, $status, $last_id = 1 )
    {
        return $query->where('id', '>', $last_id)
                     ->whereQcategoryId($category_id)
                     ->whereStatus($status)
                     ->orderBy('updated_at', 'DSC');
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeAnswered($query)
    {
        return $query->whereStatus('answered')->orderBy('created_at', 'DESC');
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopePending($query)
    {
        return $query->whereStatus('pending')->orderBy('created_at', 'DESC');
    }

    /**
     * Get pending premium questions
     * @param $query
     * @param $lockedAndSkipIds
     * @return mixed
     */
    public function scopePendingPremium($query, $lockedAndSkipIds)
    {
        return $query
            ->where(function ($query){
                $query->where('is_premium', 1)->where('is_urgent', 1)->where('status', 'pending');
            })->orWhere('is_premium', 1)
            ->whereNotIn('id', $lockedAndSkipIds)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->take(1);
    }

    /**
     * Get pending question
     * @param $query
     * @param $lockedAndSkipIds
     * @return mixed
     */
    public function scopePendingQuestion($query, $lockedAndSkipIds)
    {
        return $query->whereStatus('pending')
                     ->whereIsPremium(0)
                     ->whereSpecialistId(0)
                     ->whereNotIn('id', $lockedAndSkipIds)
                     ->orderBy('created_at', 'asc')
                     ->take(1);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeSearchAnswered($query){
        return $query->where('status','answered');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responseTime()
    {
        return $this->hasMany(ResponseTime::class);
    }

    public function subscriberPremium()
    {
        return $this->belongsTo('App\SubscriberPremium');
    }

    /**
     * Has many relationship with Notification Specialists table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function specialistNotification()
    {
        return $this->hasMany(NotificationSpecialists::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relationship with SendSMSLog table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sendSmsLogs()
    {
        return $this->hasOne(SendSMSLog::class, 'question_id');
    }

    /**
     * Relationship with draft_answer table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function draft_answer()
    {
        return $this->hasOne(DraftAnswer::class, 'question_id');
    }

    /**
     * Relationship with followup table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function followup()
    {
        return $this->hasOne(FollowUp::class, 'question_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function refer()
    {
        return $this->hasOne(Refer::class, 'question_id');
    }

    public function aiResponseLog()
    {
        return $this->hasOne(AIResponseLog::class);
    }

    public function commentQuestions()
    {
        return $this->hasMany(CommentQuestion::class);
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




