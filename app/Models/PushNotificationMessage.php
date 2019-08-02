<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotificationMessage extends Model implements \Countable
{
    protected $guarded = ['id'];

    public function save_message($request) {

//        $data = PushNotificationMessage::firstOrCreate([
//            'title' => $request->titles,
//            'details_text' => $request->details_text
//        ],[
//            'title' => $request->titles,
//            'body' => $request->bodies,
//            'noti_type' => $request->noti_type,
//            'action_type' => $request->action_type,
//            'class_type' => $request->class_type,
//            'class_name' => $request->class_name,
//            'promo_code' => $request->promo_code,
//            'url' => $request->url,
//            'image_url' => $request->image_url,
//            'header_text' => $request->header_text,
//            'details_text' => $request->details_text,
//            'btn_text' => $request->btn_text,
//            'log_in_needed' => $request->log_in_needed,
//            'question_id' => $request->question_id,
//            'noti_task' => $request->noti_task,
//            'action_data' => $request->action_data
//        ])->toArray();

        $data = PushNotificationMessage::find($request->pnm_id);
        dd($data);
        $user = $this->save_user($data['id'], $request);

        $d = array_merge($data, $user);

        return $d;

    }

    public function save_user($request) {

        if($request->status == 'receive')
            $user = PushNotificationUsers::create([
                'user_id' => $request->user_id ?? '',
                'device_id' => $request->device_id,
                'status' => $request->status,
                'pnm_id' => $request->pnm_id
            ])->toArray();
        else {
            $data = PushNotificationUsers::where('device_id', $request->device_id)
                ->where('pnm_id', $request->pnm_id)
                ->where('status', 'receive')
                ->first();
            $data->status = $request->status;
            $data->save();
            $user = $data->toArray();
//            $user = PushNotificationUsers::update($data,[
//                'user_id' => $request->user_id ?? '',
//                'device_id' => $request->device_id,
//                'status' => $request->status,
//                'pnm_id' => $request->pnm_id
//            ])->toArray();
        }

        return $user;
    }

    function user(){
        $this->belongsToMany(PushNotificationUsers::class, 'pnm_id');
    }

    private $count = 0;

    public function count()
    {
        // TODO: Implement count() method.
        return ++$this->count;
    }
}
