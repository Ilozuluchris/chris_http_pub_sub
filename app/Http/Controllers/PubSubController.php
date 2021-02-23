<?php

namespace App\Http\Controllers;

use App\Services\PubSubService;
use Illuminate\Http\Request;

class PubSubController extends Controller
{

    public function __construct(public PubSubService $pubSubService,)
    {
        //
    }


    public function publish(Request $request, string $topic){
        $msg_data = $request->post();
        return $this->pubSubService->publish($topic, $msg_data);

    }

    public function subscribe(Request $request, string $topic){
        $subscriber = $request->post('url'); // would not work with the current testing setup in the assignment because the content-type header is missing
        return $this->pubSubService->subscribe($topic, $subscriber);

    }

    public function consume(Request $request){
        $subscriber = $request->url();
        $re_consume = (bool) $request->input('reconsume');
        return $this->pubSubService->consume($subscriber, $re_consume);

    }

}
