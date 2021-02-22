<?php

namespace App\Services;

class PubSubService
{

    public function __construct()
    {
        //todo:connect to redis here
    }

    public function publish($topic, $msg){

        //todo implement; save to redis store under topic name

        return [$topic, $msg];
    }

    public function subscribe($topic, $subscriber){
        //todo implement
        // use subscriber as key has string or the hash value ; then add the topic to the list it maps to
        return [$topic, $subscriber];
    }


    public function consume($subscriber){
        //todo implement
        // figure out to  save last consumed msg
        // should this be a websocket or js thing
        //get messages
    }

}