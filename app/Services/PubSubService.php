<?php

namespace App\Services;

use Predis;

class PubSubService
{

    public function __construct()
    {
        $this->redis_client = new Predis\Client();

        try {
            //todo make redis url environment variable
            $this->redis_client->connect();
        } catch (Predis\Connection\ConnectionException $exception) {
            // We could not connect to Redis! Your handling code goes here.
            throw  $exception;
        }

    }

    public function publish(string $topic, $msg){
        //todo add time to live for message
        try{
           $this->redis_client->rpush($topic, $msg);
        }
        catch (\Exception $exception){
            return ['status'=>'Message not published', 'reason'=>$exception->getMessage()];
        }

        return ['status'=>$msg. ' published to '. $topic];
    }

    public function subscribe(string $topic, string $subscriber){
        //todo implement
        try{
            $this->redis_client->rpush($subscriber, $topic);
        }
        catch (\Exception $exception){
            return ['status'=> $subscriber . ' not subscribed to ' . $topic, 'reason'=>$exception->getMessage()];
        }

        return ['status'=> $subscriber . ' has subscribed to ' . $topic];
    }

    //todo for clean up run a command every minute or s0
    public function consume($subscriber){
        //todo implement
        // figure out to  save last consumed msg
        // should this be a websocket or js thing
        //get messages
        $messages=[];
        $subscribed_topics = $this->redis_client->lrange($subscriber, 0, -1);


        foreach ($subscribed_topics as $i=>$topic) {
            $messages[$topic]=$this->redis_client->lrange($topic, 0, -1);
        }

        return $messages;
    }

}