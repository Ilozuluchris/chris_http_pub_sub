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

    //

    public function publish(Request $request, string $topic){
        $msg = json_encode($request->post());
        return $this->pubSubService->publish($topic, $msg);

    }

    public function subscribe(Request $request, string $topic){
        $subscriber = $request->post('url'); // would not work with the current testing route because the content-type header is missing
        return $this->pubSubService->subscribe($topic, $subscriber);

    }

    public function consume(Request $request){
        $subscriber = $request->fullUrl();
        return $this->pubSubService->consume($subscriber);

    }

}
