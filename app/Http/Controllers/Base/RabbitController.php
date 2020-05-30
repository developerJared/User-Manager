<?php
/**********************************
 * CREATED AS A TEST FOR MESSAGING
 *********************************/
namespace App\Http\Controllers\Base;

use PhpAmqpLib\Connection\AMQPStreamConnection as RabbitStream;
use PhpAmqpLib\Message\AMQPMessage as Message;

class Rabbit
{
    /*****************************************************
     * MESSAGE QUEUE TESTING FOR NEAR REALTIME USER SYNC
     * @return string
     ***************************************************/
    public function RabbitTest()
    {
        $Rabbit = ConfigurationController::getMessageServiceSettings();
        $connection = new RabbitStream($Rabbit['address'], $Rabbit['port'], 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('TestQ', false, true, false, false);
        $msg = new Message("String message"); //Pass user access object here.
        $channel->basic_publish($msg, '', 'TestQ');
        $channel->close();
        $connection->close();
        return "Ran Rabbit";
    }

}