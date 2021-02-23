# Introduction
This project is an attempt to replicate pub/sub functionality using HTTP.  
It was built using PHP8, [Lumen](https://lumen.laravel.com/docs) and redis. It was tested on Ubuntu 20.04


## Setup
- Install docker and confirm you do not need super user privileges to use it

## Testing
- Run `./start-server.sh` to start the server
- Run `curl -X POST -H 'Content-Type: Application/json'  -d '{"url":"http://localhost:8000/event"}' http://localhost:8000/subscribe/topic1` to subscribe `http://localhost:8000/event` to `topic1`
- Run `curl -X POST -H "Content-Type: application/json" -d '{"message": "hello"}' http://localhost:8000/publish/topic1` to publish a message to topic `topic1`
- Run `curl http://localhost:8000/event` or [visit in the browser](http://localhost:8000/event) to print(consume) messages that `http://localhost:8000/event` has received
- Note: Once messages are printed(consumed) they can not be consumed again. To consume again, make a curl call to `http://localhost:8000/event?reconsume=true` or [visit in the browser](http://localhost:8000/event?reconsume=true)
- Side note: viewing in the browser renders the data better :) 
