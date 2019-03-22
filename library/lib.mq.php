<?php
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

require_once(__DIR__.'/'.'../vendor/autoload.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

class Lib_MQ
{
    private $connection;
    private $channel;
    private $queue;
    private $corr_id;
    private $response;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(MQ_IP, MQ_PORT, MQ_USERNAME, MQ_PWD,MQ_VHOST);
        //创建一个通道
        $this->channel = $this->connection->channel();

        list($this->queue, ,) = $this->channel->queue_declare('', false, false, true, true);

        $this->channel->basic_consume($this->queue,'',false,true,false,false,array($this,'request_callback'));

    }


    /**
     * 发消息
     * @param $request '需要发送的数据'
     * @param $exchange
     * @param $route
     * @return string
     */
    public function send_rpc($request,$exchange,$route) {
        $request = json_encode($request,JSON_UNESCAPED_UNICODE);
        $this->corr_id = "d-".uniqid();
        $msg = new AMQPMessage((string) $request, array('correlation_id' => $this->corr_id,'reply_to' => $this->queue));
        $this->channel->basic_publish($msg, $exchange, $route);

        /**
         * 等待处理结果，如果3s内都没处理完，则返回timeout
         */
        while(!$this->response) {
            try{
                $this->channel->wait(null,false,3);
            }catch (AMQPTimeoutException $e){
                $this->response = json_encode(array('r'=>'1','p'=>array('m'=>'timeout')));
                return $this->response;
                break;
            }
        }
        return json_decode($this->response,true);
    }

    /**
     * @param $req
     */
    public function request_callback($req){
        if($req->get('correlation_id') == $this->corr_id) {
            $this->response = $req->body;
        }
    }

    /**
     * 发布广播消息
     * @param $request  '数据'
     * @param $exchange
     * @param $route
     */
    public function send_notice($request,$exchange,$route){
        $this->corr_id = uniqid();
        $msg = new AMQPMessage((string) $request, array('correlation_id' => $this->corr_id));
        $this->channel->basic_publish($msg, $exchange, $route);
    }
}
