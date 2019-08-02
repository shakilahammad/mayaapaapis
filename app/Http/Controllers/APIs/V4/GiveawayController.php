<?php
/**
 * Created by PhpStorm.
 * User: jarman
 * Date: 6/23/19
 * Time: 1:59 PM
 */
namespace App\Http\Controllers\APIs\V4;
use App\giveaway_tickets;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GiveawayTickets;

class GiveawayController extends Controller{

    public function getUserGiveawayStatus($userId){

       $product_id =  $this->getUserGiveawayProductId();
       $status = DB::select("select sum(s.value) as total from `giveaway_ticket_sources` s , `giveaway_tickets` t where t.`source_id` = s.id and t.`product_id` = $product_id");
       if($status != null && $status->total > 0){
           return response()->json($status);
       }
        return response()->json(
            [ 'status' => 'failed']
        );
    }

    public function getUserGiveawayProduct(){
      $products =   DB::select("select * from `giveaway_products` where expire_date > NOW() ORDER BY id desc limit 1");

      if(count($products)>0){

          return response()->json($products);

      }else{

          return response()->json(

             [ 'status' => 'failed']

          );
      }

    }

    public function getUserGiveawayProductIdAndName($product_id){
        $products =   \DB::select("select * from `giveaway_products` where id = $product_id");

        if(count($products)>0){

            return $products;

        }

        return 0;

    }


    public function getUserGiveawayHistory(){

        $products =   \DB::select("select * from `giveaway_lotteries` ORDER BY id desc limit 6");
        if(count($products)>0){

            return response()->json($products);

        }else{

            return response()->json(

                [ 'status' => 'failed']

            );
        }

    }

    public function getUserGiveawayEntry($userId,$product_id){

//        $count1 =   $this->curl_get_shares("https://maya.com.bd/giveaway/$userId/");
//        $count2 =   $this->curl_get_shares("https://maya.com.bd/giveaway/1023148");

        $product =  $this->getUserGiveawayProductIdAndName($product_id);

//dd($product[0]->name);
        if($product!=0){

            $count1 =   $this->curl_get_shares("https://maya.com.bd/giveaway/$userId/".$product[0]->name);
            $count2 =   $this->curl_get_shares("https://maya.com.bd/giveaway/1023148/$userId/".$product[0]->name);
            $ticket =   giveaway_tickets::firstOrNew(array('product_id' => $product[0]->id, 'user_id' => $userId, 'source_id' => 1 ));
            $ticket->value = $count1;
            $ticket->save();
            $ticket = giveaway_tickets::firstOrNew(array('product_id' => $product[0]->id, 'user_id' => $userId, 'source_id' => 2 ));
            $ticket->value = $count2;
            $ticket->save();

            return response()->json(
                [
                    "count1" => $count1*2,
                    "count2" => $count2*5,
                ]
            );
        }


//        $status = DB::select("select sum(s.value) as total from `giveaway_ticket_sources` s , `giveaway_tickets` t where t.`source_id` = s.id and t.`product_id` = $product_id");
//        if($status != null && $status->total > 0){
//            return response()->json($status);
//        }
//        return response()->json(
//            [ 'status' => 'failed']
//        );
    }

    public function curl_get_shares( $url ){
        $access_token = '523737687789468|090c0b3ab1b62b6a9c93ad71f92487d1';
        $api_url = 'https://graph.facebook.com/v3.0/?id=' . urlencode( $url ) . '&fields=engagement&access_token=' . $access_token;
        $fb_connect = curl_init(); // initializing
        curl_setopt( $fb_connect, CURLOPT_URL, $api_url );
        curl_setopt( $fb_connect, CURLOPT_RETURNTRANSFER, 1 ); // return the result, do not print
        curl_setopt( $fb_connect, CURLOPT_TIMEOUT, 20 );
        $json_return = curl_exec( $fb_connect ); // connect and get json data
        curl_close( $fb_connect ); // close connection
        $body = json_decode( $json_return );
        return intval( $body->engagement->share_count );
    }
}