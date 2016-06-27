<?php
namespace App\Controller;

use App\Controller\AppController;

use Pubnub\Pubnub;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class PubnubController extends AppController
{

	public function initialize()
    {
    	
		parent::initialize();
		$this->loadComponent('RequestHandler');
		//$this->RequestHandler->config('inputTypeMap.json', ['json_decode', true]);
   
	}
    
	public function beforeFilter(\Cake\Event\Event $event)
	{
		$this->Auth->allow(['send']);
		parent::beforeFilter($event);		
	}
	
	public function abc(){
		$this->autoRender=false;
		try{

		}catch(Exception $ex){
			echo $ex->getMessage();
		}
		echo "in abc function";

	}
   
    public function send(){ 
        $status=200;
    	$this->autoRender=false;
    	$jsonKey=array("type", "channel_id", "content_type", "content", "message_id");
    	if($this->request->is('post')){
    		$request_data = $this->request->input('json_decode', true);
    		if($this->validateJsonKey($request_data, $jsonKey)){
    			$request_data['date_time'] = time();
    			$request_data['sender_id']= $this->session_user_id;
    			$request_data['user']=array(
                    "name"=>$this->session_user_first_name." ".$this->session_user_last_name,
                    "avatar"=>'',
                    "user_id"=>$this->session_user_id);

    			if($request_data['type']=="individual"){
	                $chatStatus= $this->individualChat($request_data, $this->session_user_id);
	                $success=$chatStatus['status'];
	                $return_data=$chatStatus['message'];
		        }elseif($input['type']=="group"){



		        }    		
	    	}else{
	    		$status  = 400;
				$success = false;
				$return_data = "Invalid json data";
	    	}
    	}else{
    		$status  = 400;
			$success = false;
			$return_data = "Method not allowed, Method should be post";
    	}    	

    	$this->response->type('json');
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success));
		$this->response->statusCode($status);
		$this->response->body($json);
    }
    
	
	public function validateJsonKey($input, $jsonKey) {
        try {
            foreach ($jsonKey as $val) {
                if (!array_key_exists($val, $input) || ($input[$val] == '')) {
                    #step1
                    return FALSE;
                }
            }
        } catch (Exception $ex) {
            return FALSE;
        }
        return TRUE;
    }
	
	######################## Individual Chat ##########################
    public function individualChat($input, $sender_id){
        
        try{            
            $channel_id= array(
                    $input['channel_id'],
                    $sender_id
                );
            $this->loadModel('Users');
            $this->regrantToPubnub($channel_id);
            $message= array('message' => $input);
            foreach ($channel_id as  $value) {
                if($message['message']['channel_id']==$input['channel_id']){

                    $userInfo = $this->Users->find('all')->where(['Users.id'=>$sender_id])->first()->toArray();
                   
                    $message['message']['channel_id']= $sender_id;
                    $message['message']['user']['name']= $userInfo['first_name']." ".$userInfo['last_name'];
                    $message['message']['user']['avatar']= '';
                    $message['message']['user']['user_id']= $userInfo['id'];

                }elseif($message['message']['channel_id']==$sender_id){
                    $userInfo = $this->Users->find('all')->where(['Users.id'=>$input['channel_id']])->first()->toArray();
                    
                    $message['message']['channel_id']= $input['channel_id'];
                    $message['message']['user']['name']= $userInfo['first_name']." ".$userInfo['last_name'];
                    $message['message']['user']['avatar']= '';
                    $message['message']['user']['user_id']= $userInfo['id'];
                }
              
                $publishStatus=$this->pubnub->publish(md5($value), $message);                          
            }
            return array("status"=>true, "message"=>"");

        }catch(Exception $ex){
            return array("status"=>false, "message"=>$ex->getMessage());

        }
    }
	
	public function regrantToPubnub($channel) {
        $this->pubnub = new Pubnub(PUBLISH_KEY, SUBSCRIBE_KEY, SECRET_KEY, CIPHER_KEY, SSL_ON);
        if(count($channel) > 0){
            foreach ($channel as $value) {
                $this->pubnub->grant(true, true, md5($value), AUTH_KEY, 0);
            }
        }
    }
	
}
