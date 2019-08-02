<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    public function fetchSingleArticle($articleId)
    {
        $article = DB::table('wp_posts')
            ->select(['ID', 'post_title', 'post_content', 'post_modified_gmt', 'guid'])
            ->where('ID', $articleId)
            ->where('post_status', 'publish')
            ->where('post_parent', 0)
            ->whereRaw('LENGTH(post_content) > ?', [100])
            ->first();

        if (!$article) {
            return response()->json(
                $this->errorResponse('Article not found!')
            );
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->formattedSingleArticle($article),
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    public function formattedSingleArticle($article)
    {
        $imageUrl = DB::table('wp_posts')
            ->where('post_parent', $article->ID)
            ->where('post_type', 'attachment')
            ->first(['guid']);

        return [
            'title' => $article->post_title,
            'image_url' => !empty($imageUrl) ? $imageUrl->guid : '',
            'content' => $article->post_content,
            'created_at' => $article->post_modified_gmt,
            'web_url' => config('app.url') . 'content/web/wp/' . $article->ID
        ];
    }

    public function fetchArticles($language = 'en', $offset = 0, $limit = 10, $category = 0)
    {
        if ($language == 'en') {
            $lang = 'REGEXP';
        } else if ($language == 'bn') {
            $lang = 'NOT REGEXP';
        } else {
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "Language not supported"
                ]
            ]);
        }

//        $category = request()->input('category');
//        $cat = DB::select('select `count` from wp_term_taxonomy wtt left join wp_posts where term_id = ' . $category);
//        dd($cat);

        if($category>0)
            $and = 'and t.parent = (SELECT (case when(t1.cnt > 0) THEN t1.parent ELSE 0 END) FROM (SELECT count(t.term_id) as cnt, t.parent FROM wp_term_relationships AS r, wp_term_taxonomy AS t, wp_posts AS p WHERE r.term_taxonomy_id = t.term_taxonomy_id AND t.term_id = ' . $category . ' AND p.ID = r.object_id AND p.post_status = "publish") as t1)';
        else
            $and = '';

        try {
            $ar1 = [];
            if($category==0){
                $article1 = DB::table('wp_posts')
                    ->where('post_status', 'publish')
                    ->where('post_parent', 0)
                    //                ->where('post_title', $lang, '[a-z]')
                    //                ->whereRaw('LENGTH(post_content) > ?', [100])
                    //                ->whereRaw('id IN (select r.object_id from wp_term_relationships as r, wp_term_taxonomy as t where r.term_taxonomy_id = t.term_taxonomy_id ' . $and . ')')
//                    ->whereIn('id', ['8451', '8446'])
                    ->join('wp_postmeta as pm1', 'wp_posts.id', '=', 'pm1.post_id')
                    ->join('wp_postmeta as pm2', 'pm1.meta_value', '=', 'pm2.post_id')
                    ->where('pm2.meta_key', '=', '_wp_attached_file')
                    ->where('pm1.meta_key', '=', '_thumbnail_id')
                    ->skip($offset)
                    ->take($limit)
                    ->inRandomOrder()
                    ->orderBy('ID', 'desc')
                    ->get(['ID', 'post_title', 'post_content', 'post_modified_gmt', DB::raw('CONCAT("https://www.maya.com.bd/content/web/wp/wp-content/uploads/", pm2.meta_value) AS img_url')]);
//                    ->toSql();
//                dd($article1);
                $ar1 = $this->getFormattedArticle($article1);
            }

            $article = DB::table('wp_posts')
                ->where('post_status', 'publish')
                ->where('post_parent', 0)
//                ->where('post_title', $lang, '[a-z]')
//                ->whereRaw('LENGTH(post_content) > ?', [100])
                ->whereRaw('id IN (select r.object_id from wp_term_relationships as r, wp_term_taxonomy as t where r.term_taxonomy_id = t.term_taxonomy_id ' . $and . ')')
//                ->orWhereIn('id', ['8451', '8446'])
                ->join('wp_postmeta as pm1', 'wp_posts.id', '=', 'pm1.post_id')
                ->join('wp_postmeta as pm2', 'pm1.meta_value', '=', 'pm2.post_id')
                ->where('pm2.meta_key', '=', '_wp_attached_file')
                ->where('pm1.meta_key', '=', '_thumbnail_id')
                ->skip($offset)
                ->take($limit)
                ->inRandomOrder()
                ->orderBy('ID', 'desc')
//                    ->toSql();
                ->get(['ID', 'post_title', 'post_content', 'post_modified_gmt', DB::raw('CONCAT("https://www.maya.com.bd/content/web/wp/wp-content/uploads/", pm2.meta_value) AS img_url')]);
//                ->toSql();
//            dd($article);

            $ar2 = $this->getFormattedArticle($article);
            $arr = array_merge($ar1, $ar2);
//dd($arr);
            return response()->json([
                'status' => 'success',
                'data' => $arr
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "No articles found!"
                ]
            ]);
        }
    }

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

//        $category = request()->input('category');

        if($keyword){
            $tagids = 'select term_id from `wp_terms` where `name` LIKE "%'.$keyword.'%"';
            $and = 'and t.term_id IN (' . $tagids . ')';
        }
        else
            $and = '';
        try {
//            $article1 = DB::table('wp_posts')
//                ->where('post_status', 'publish')
//                ->where('post_parent', 0)
////                ->where('post_title', $lang, '[a-z]')
////                ->whereRaw('LENGTH(post_content) > ?', [100])
////                ->whereRaw('id IN (select r.object_id from wp_term_relationships as r, wp_term_taxonomy as t where r.term_taxonomy_id = t.term_taxonomy_id ' . $and . ')')
//                ->whereIn('id', ['8451', '8446'])
//                ->join('wp_postmeta as pm1', 'wp_posts.id', '=', 'pm1.post_id')
//                ->join('wp_postmeta as pm2', 'pm1.meta_value', '=', 'pm2.post_id')
//                ->where('pm2.meta_key', '=', '_wp_attached_file')
//                ->where('pm1.meta_key', '=', '_thumbnail_id')
////                ->skip($offset)
////                ->take($limit)
////                ->inRandomOrder()
//                ->orderBy('ID', 'desc')
//                ->get(['ID', 'post_title', 'post_content', 'post_modified_gmt', DB::raw('CONCAT("https://www.maya.com.bd/content/web/wp/wp-content/uploads/", pm2.meta_value) AS img_url')]);

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
                //DB::raw('CONCAT("https://www.maya.com.bd/content/web/wp/wp-content/uploads/", pm2.meta_value) AS img_url')

//            $ar1 = $this->getFormattedArticle($article1);
//            $ar2 = $this->getFormattedArticle($article);
//            $arr = array_merge($ar1, $ar2);
//dd($arr);
            return response()->json([
                'status' => 'success',
                'data' => $this->getFormattedArticle($article)
            ]);
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
//        $this->fetchSingleArticle();
//        dd($articles);
        foreach ($articles as $article) {
            $values = [
                'id' => $article->ID,
                'post_title' => $article->post_title,
                'image_source' => $article->img_url,
                'post_content' => $article->post_content,
                'created_at' => $article->post_modified_gmt,
            ];
            array_push($data, $values);
        }
        return $data;
    }

    private function errorResponse($message)
    {
        return [
            'status' => 'failure',
            'data' => [
                'error' => $message
            ],
            'error_code' => 0,
            'error_message' => ''
        ];
    }

}
