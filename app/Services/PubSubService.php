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

    public function publish(string $topic, $msg_data){
        //todo add time to live for message
        try{
           $lid = $this->redis_client->llen($topic);
           $msg = json_encode(['data'=>$msg_data, 'msg_id'=>$lid]);
           $this->redis_client->rpush($topic, $msg);
        }
        catch (\Exception $exception){
            return ['success'=> False, 'msg'=>'Message not published because '. $exception->getMessage()];
        }

        return ['success'=> True, 'msg'=>json_encode($msg_data). ' published to '. $topic];
    }

    public function subscribe(string $topic, string $subscriber){
        try{
            $this->redis_client->rpush($subscriber, $topic);
        }
        catch (\Exception $exception){
            return ['status'=> $subscriber . ' not subscribed to ' . $topic, 'reason'=>$exception->getMessage()];
        }

        return ['status'=> $subscriber . ' has subscribed to ' . $topic];
    }

    //todo for clean up run a command every minute or s0
    public function consume($subscriber, bool $reconsume_from_begining=False){
        // should this be a websocket or js thing
        //get messages
        $messages=[];
        $subscribed_topics = $this->redis_client->lrange($subscriber, 0, -1);


        foreach ($subscribed_topics as $i=>$topic) {

            $messages[$topic]= $this->getSubMessageForTopic($topic, $subscriber, $reconsume_from_begining);

        }

        return $messages;
    }

    private function getSubMessageForTopic($topic, $subscriber, $reconsume){
        $start = $reconsume ? 0: $this->getLastConsumedMsgId($topic, $subscriber)+1;
        $msgs = $this->redis_client->lrange($topic,  $start, -1);
        $result = [];

        //todo maybe while loop better here so last item is not accessed twice
        foreach($msgs as $i=>$msg){
            array_push($result, json_decode($msg, true)['data']);
        }

        // Sometimes there is no new message to consume.
        if (!empty($msgs)){
            $this->setLastConsumedMsgId($topic, $subscriber, $msgs[array_key_last($msgs)]);
        }

        return $result;
    }


    private function getLastConsumedMsgId($topic, $subscriber)
    {

        try{
            $last_consumed_array = json_decode($this->redis_client->get('last_consumed'), true);
            $msg_id = $last_consumed_array[$subscriber][$topic];
        }
        catch (\Exception $e){
            // if no consumption has happened before start consuming from the  beginning
            return 0;
        }

        return $msg_id;
    }

    private function setLastConsumedMsgId($topic, $subscriber, $last_msg)
    {
        $last_msg_id =  json_decode($last_msg, true)['msg_id'];
        $last_consumed_array = json_decode($this->redis_client->get('last_consumed'), true);
        $last_consumed_array[$subscriber][$topic] = $last_msg_id;
        $this->redis_client->set('last_consumed', json_encode($last_consumed_array));
    }

}