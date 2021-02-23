<?php

namespace App\Services;

use Predis;

/***
 * Class PubSubService
 *  Responsible for the actual pub/sub, it uses redis to achieve this.
 * @package App\Services
 */
class PubSubService
{

    public function __construct()
    {
        $this->redis_client = new Predis\Client(getenv('REDIS_URL'));

        try {
            $this->redis_client->connect();
        } catch (Predis\Connection\ConnectionException $exception) {
            throw  $exception;
        }

    }

    public function publish(string $topic, $msg_data){
        /***
         * Publishes a message to a topic by adding to a list for that topic
         */
        //todo add time to live for message or for clean up run a command every minute or s0
        // since ideally pub/sub infrastructure do not store messages forever
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

    public function consume($subscriber, bool $reconsume_from_begining=False){
        /**
         * Get topics subscriber has subscribed to, then get messages in those topics
         */
        $messages=[];
        $subscribed_topics = $this->redis_client->lrange($subscriber, 0, -1);


        foreach ($subscribed_topics as $i=>$topic) {

            $messages[$topic]= $this->getMessages($topic, $subscriber, $reconsume_from_begining);

        }

        return $messages;
    }

    private function getMessages($topic, $subscriber, $reconsume){
        $start = $reconsume ? 0: $this->getLastConsumedMsgId($topic, $subscriber)+1;
        $msgs = $this->redis_client->lrange($topic,  $start, -1);
        $result = [];

        foreach($msgs as $i=>$msg){
            array_push($result, json_decode($msg, true)['data']);
        }

        // Sometimes there is no new message in the topic to consume.
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
            // if no consumption has happened before, start consuming from the beginning
            return 0;
        }

        return $msg_id;
    }

    private function setLastConsumedMsgId($topic, $subscriber, $last_msg){
        /***
         * Save the last consumed message via its msg_id,
         * so previously consumed messages are not consumed again.
         */
        $last_msg_id =  json_decode($last_msg, true)['msg_id'];
        $last_consumed_array = json_decode($this->redis_client->get('last_consumed'), true);
        $last_consumed_array[$subscriber][$topic] = $last_msg_id;
        $this->redis_client->set('last_consumed', json_encode($last_consumed_array));
    }

}