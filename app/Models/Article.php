<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Article extends Model implements \Countable
{
    protected $table = 'wp_posts';
    protected $fillable =
        [
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_title',
            'post_status',
            'guid',
            'post_status'
        ];
    private $count = 0;

    // protected $dates = ['deleted_at'];

    // public function getTitleEnAttribute($value){
    // 	return utf8_decode($value);
    // }

    // public function getTitleBnAttribute($value){
    // 	return utf8_decode($value);
    // }

    // public function getContentEnAttribute($value){
    // 	return utf8_decode($value);
    // }

    // public function getContentBnAttribute($value){
    // 	return utf8_decode($value);
    // }

    // function SubCategory()
    // {
    // 	return $this->belongsTo('SubCategory', 'subcategory_id');
    // }

    // function Comments()
    // {
    // 	return $this->hasMany('Comment');
    // }

    // function Notifications()
    // {
    // 	return $this->hasMany('Notification');
    // }

    // function Likes()
    // {
    // 	return $this->hasMany('Like', 'article_id');
    // }

    // function Author()
    // {
    // 	return $this->belongsTo('User', 'author_id');
    // }

    // function Children()
    // {
    // 	return $this->hasMany('Article', 'parent_id');
    // }

    // function Parent()
    // {
    // 	return $this->belongsTo('Article', 'parent_id');
    // }

    // function scopeWithStatusAndSubCategory($query, $status = 'published', $subcategory)
    // {
    // 	return $query->whereHas('SubCategory', function ($q) use ($status, $subcategory)
    // 	{
    // 		$q->where('status', '=', $status)->where('subcategory_id', '=', $subcategory);
    // 	});
    // }

    // function scopeWithoutParentsWithStatusAndSubCategory($query, $status = 'published', $subcategory)
    // {
    // 	return $query
    // 	->whereStatus($status)
    // 	->whereSubcategoryId($subcategory)
    // 	->whereParentId(0);
    // }

    // function scopeWithSubCategoryAndOffset($query, $subcategory, $offset)
    // {
    // 	return $query->whereSubcategoryId($subcategory)->where('id', '>', $offset);
    // }

    // function scopeFindRecentArticlesByType($query, $type, $limit = 1)
    // {
    // 	if ($type == 'featured') {
    // 		return $query->whereFeatured(true)->orderBy('created_at', 'desc')->take($limit);
    // 	}else{
    // 		return $query->whereEditorsPick(true)->orderBy('created_at', 'desc')->take($limit);
    // 	}
    // }

    public function searchArticles($offset = 0, $limit = 10, $keyword = 'health')
    {
//        if ($language == 'en') {
//            $lang = 'REGEXP';
//        } else if ($language == 'bn') {
//            $lang = 'NOT REGEXP';
//        } else {
//            return response()->json([
//                'status' => 'failure',
//                'data' => [
//                    'error' => "Language not supported"
//                ]
//            ]);
//        }

        try {
            if($keyword){
                $tagids = 'select term_id from `wp_terms` where `name` LIKE "%'.$keyword.'%"';
                $and = 'and t.term_id IN (' . $tagids . ')';
            } else
                $and = '';

            $article = DB::table('wp_posts')
                ->where('post_status', 'publish')
                ->where('post_parent', 0)
                ->where('post_type', 'post')
                ->whereRaw('LENGTH(post_content) > 100')
                ->whereRaw('((id IN (select r.object_id from wp_term_relationships as r, wp_term_taxonomy as t where r.term_taxonomy_id = t.term_taxonomy_id ' . $and . ')) OR `post_title` LIKE "%' . $keyword . '%")')
                ->join('wp_postmeta as pm1', 'wp_posts.id', '=', 'pm1.post_id')
                ->join('wp_postmeta as pm2', 'pm1.meta_value', '=', 'pm2.post_id')
                ->where('pm2.meta_key', '=', '_wp_attached_file')
                ->where('pm1.meta_key', '=', '_thumbnail_id')
                ->skip($offset)
                ->take($limit)
                ->inRandomOrder()
                ->get(['ID', 'post_title', 'post_content', 'post_modified_gmt', DB::raw('CONCAT("https://www.maya.com.bd/content/web/wp/wp-content/uploads/", pm2.meta_value) AS img_url')]);

            if(count($article)==0)
                $article = DB::table('wp_posts')
                    ->where('post_status', 'publish')
                    ->where('post_parent', 0)
                    ->where('post_type', 'post')
                    ->whereRaw('LENGTH(post_content) > 100')
                    ->whereRaw('(post_title LIKE "%' . $keyword . '%") OR (wp_posts.post_content LIKE "%' . $keyword . '%")')
                    ->join('wp_postmeta as pm1', 'wp_posts.id', '=', 'pm1.post_id')
                    ->join('wp_postmeta as pm2', 'pm1.meta_value', '=', 'pm2.post_id')
                    ->where('pm2.meta_key', '=', '_wp_attached_file')
                    ->where('pm1.meta_key', '=', '_thumbnail_id')
                    ->skip($offset)
                    ->take($limit)
                    ->inRandomOrder()
                    ->get(['ID', 'post_title', 'post_content', 'post_modified_gmt', DB::raw('CONCAT("https://www.maya.com.bd/content/web/wp/wp-content/uploads/", pm2.meta_value) AS img_url')]);
            //DB::raw('CONCAT("https://www.maya.com.bd/content/web/wp/wp-content/uploads/", pm2.meta_value) AS img_url')

            return $this->getFormattedArticle($article);

        }catch (\Exception $exception){

            return response()->json([
                'status' => 'failure',
                'data' => null
            ]);

        }
    }

    public function getFormattedArticle($articles)
    {
        $data = [];
        foreach ($articles as $article) {
            $values = [
                'id' => $article->ID,
                'post_title' => $article->post_title,
                'image_source' => $article->img_url,
                'post_content' => $article->post_content,
                'created_at' => $this->getFormattedTime($article->post_modified_gmt),
            ];
            array_push($data, $values);
        }
        return $data;
    }

    private function getFormattedTime($time): string
    {
        return Carbon::parse($time)->diffForHumans();
    }

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
