<?php

namespace App\Http\Controllers\ApiV1;

use Carbon\Carbon;
use App\Models\DynamicCard;
use App\Models\DynamicCardActivity;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

class DynamicCardController extends Controller
{
    public function index($userId = null)
    {
        try {
            $priorityCards = new Collection();
            $priorityCardsCount = 0;
            $dynamicCardType = $this->getDynamicCardType();

//            if ($dynamicCardType === 'image') {
//                $priorityCards = DynamicCard::whereCardType($dynamicCardType)->where('priority', '>', 0)->OrderBy('priority', 'desc')->take(6)->get();
//                $priorityCardsCount = $priorityCards->count();
//            }
//
//            $cardsCount = 6 - $priorityCardsCount;
//
//            if (!empty($userId)) {
//                $cancelCards = DynamicCardActivity::where('user_id', $userId)->where('cancel', true)->get();
//
//                $cancelCardIds = $cancelCards->pluck('dynamic_card_id');
//
//                if ($userId == 28414) {
//                    $cards = DynamicCard::whereNotIn('id', $cancelCardIds)->take($cardsCount)->orderBy('id', 'desc')->get();
//                } else {
//                    $cards = DynamicCard::whereCardType($dynamicCardType)->whereNotIn('id', $cancelCardIds)->inRandomOrder()->take($cardsCount)->get();
//                }
//            }else{
//                $cards = DynamicCard::whereCardType($dynamicCardType)->inRandomOrder()->take($cardsCount)->get();
//            }


            if ($dynamicCardType === 'image') {
                $priorityCards = DynamicCard::whereCardType($dynamicCardType)->where('priority', '>', 0)->OrderBy('priority', 'desc')->take(6)->get();
                $priorityCardsCount = $priorityCards->count();
            }

            $cardsCount = 6 - $priorityCardsCount;

            if (!empty($userId)) {
                $cancelCards = DynamicCardActivity::where('user_id', $userId)->where('cancel', true)->get();

                $cancelCardIds = $cancelCards->pluck('dynamic_card_id');

                if ($userId == 28414) {
                    $cards = DynamicCard::whereCardType('image')->whereNotIn('id', $cancelCardIds)->take($cardsCount)->orderBy('id', 'desc')->get();
                } else {
                    $cards = DynamicCard::whereCardType('image')->whereNotIn('id', $cancelCardIds)->inRandomOrder()->take($cardsCount)->get();
                }
            }else{
                $cards = DynamicCard::whereCardType('image')->inRandomOrder()->take($cardsCount)->get();
            }

            return $this->makeResponse('success', $priorityCards->merge($cards));

        } catch (\Exception $exception) {
            return $this->makeResponse('failure');
        }
    }

    public function cancelCard($cardID, $userID)
    {
        try {
            DynamicCardActivity::updateOrCreate(
                ['user_id' => $userID, 'dynamic_card_id' => $cardID],
                ['cancel' => true]
            );
            return $this->makeResponse('success');
        } catch (\Exception $exception) {
            return $this->makeResponse('failure');
        }
    }

    private function transformCards($cards)
    {
        $data = [];
        foreach ($cards as $card) {
            $value = [
                'id' => $card->id,
                'title' => $card->title,
                'subtitle' => $card->subtitle,
                'action_text' => $card->action_text,
                'type' => $card->type,
                'card_type' => $card->card_type,
                'status' => $card->status,
                'priority' => $card->priority,
                'question_id' => $card->question_id,
                'article_id' => $card->article_id,
                'activity_name' => $card->activity_name,
                'fragment_name' => $card->fragment_name,
                'image_url' => $card->image_url,
                'page_url' => $card->page_url,
                'open_page' => $card->open_page,
                'for_data' => $card->for,
                'update_type' => $card->update_type,
                'time' => $card->time,
                'created_at' => $this->transforTime($card->created_at)
            ];
            array_push($data, $value);
        }

        return $data;
    }

    private function transforTime($time)
    {
        return Carbon::parse($time)->diffForHumans();
    }

    private function makeResponse($status, $data = [])
    {
        return response()->json([
            'status' => $status,
            'data' => $this->transformCards($data),
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    private function getDynamicCardType()
    {
        if (\Cache::has('dynamic')) {
            $cacheValue = \Cache::get('dynamic');
            if ($cacheValue == 'image') {
                \Cache::forever('dynamic', 'text');
            } else {
                \Cache::forever('dynamic', 'image');
            }
        } else {
            \Cache::forever('dynamic', 'image');
        }

        return \Cache::get('dynamic');
    }

}
