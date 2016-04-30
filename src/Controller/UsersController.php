<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{

	public function initialize()
    {
		parent::initialize();
		$this->loadComponent('RequestHandler');
		//$this->RequestHandler->config('inputTypeMap.json', ['json_decode', true]);
   
	}
    
	public function beforeFilter(\Cake\Event\Event $event)
	{
		$this->Auth->allow(['login','add','logout','forgotpassword','newpassword','confirmpassword']);
		parent::beforeFilter($event);		
	}
	public function isAuthorized($user)
	{
		
	}
    public function login()
	{
		try {
			$this->autoRender = FALSE;
			$this->loadmodel('UserDevices');			
			$user = $this->Users->newEntity();
			switch (true) {
					case $this->request->is('post'):
						$request_data = $this->request->input('json_decode', true);
						if(!isset($request_data['email']) OR !isset($request_data['password'])) {
							$status  = 200;
							$success = false;
							$return_data = "Data Missing";
						} else {
							if(!isset($request_data['device_type']) OR !isset($request_data['device_token'])) { 
									$status  = 200;
									$success = false;
									$return_data = "Device type and token required";
							} else {
								$user = $this->Auth->identify();
								if(!$user) {
									$status  = 200;
									$success = false;
									$return_data = "User not found";
								} else {
									$status  = 200;
									$success = true;
									$return_data = $user;
									$request_data['user_id']=$user['id'];
									$userDevice = $this->UserDevices->newEntity();
									$userDevice = $this->UserDevices->patchEntity($userDevice, $request_data);
									$this->UserDevices->save($userDevice);
			
								}
							}
						}
				break;
				default:
					$status  = 400;
					$success = false;
					$return_data = "This method not allowed";
					break;
			}
		} catch (Exception $e) {
				$data= json_encode(array('exception_message'=>$e->getMessage()));
		}
		$this->response->type('json');
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success));
		$this->response->statusCode($status);
		$this->response->body($json);
	}
    
    public function logout()
	{
		$this->autoRender = FALSE;
			$request_data = $this->request->input('json_decode', true);
		if(!isset($request_data['device_type']) OR !isset($request_data['device_token'])) {
			$status  = 200;
			$success = false;
			$return_data = "Device type and device token missing";
		} else {
			$this->loadmodel('UserDevices');
			$this->UserDevices->deleteAll(['device_type' => $request_data['device_type'],'device_token' => $request_data['device_token']]);
			
			$this->Auth->logout();
			//return $this->redirect(SITE_URL);
			$status  = 200;
			$success = true;
			$return_data = "Logged out successfully";
		}
		$this->response->type('json');
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success));
		$this->response->statusCode($status);
		$this->response->body($json);
	}

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    { 
        $this->set('users', $this->paginate($this->Users));
        $this->set('_serialize', ['users']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Bookmarks']
        ]);
        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
		try {
			
			//$this->layout =FALSE;
			$this->autoRender = FALSE;
			$user = $this->Users->newEntity();
				switch (true) {
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($request_data['email']) OR !isset($request_data['password'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
								$result = $this->Users->findByEmail($request_data['email']);	
								if($result->count()==0) {
									$user = $this->Users->patchEntity($user, $request_data);
									if ($this->Users->save($user)) {
										$status  = 200;
										$success = true;
										$return_data = "User registered successfully ";
									} else {
										$status  = 200;
										$success = false;
										$return_data = "Error while adding user";
									}
								} else {
									$status  = 200;
									$success = false;
									$return_data = "Email Already Exist";
								}
							}
							break;
						default:
							$status  = 400;
							$success = false;
							$return_data = "This method not allowed";
							break;
				}
		} catch (Exception $e) { 
				$data= json_encode(array('exception_message'=>$e->getMessage()));
				$status  = 400;
				$success = false;
				$return_data = $e->getMessage();
		}
		
		$this->response->type('json');

		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success));
		$this->response->body($json);
        //exit;
    }

	
	public function sports($user_id=''){
		
		try { 
			$this->autoRender = FALSE;	
			$this->loadmodel('SportsPreferences');		
			switch (true) {
					case $this->request->is('get'):
						if(!isset($user_id) OR $user_id=="") {
							$return_data= "User Id required";
							$success=FALSE;
							$status  = 200;
						} else {
							$return_data= $this->SportsPreferences->find()->select(['id', 'sport_id','Sports.name'])->leftJoin(['Sports' => 'sports'],['Sports.id = SportsPreferences.sport_id'])->where(['user_id'=>$user_id]);
							$success=TRUE;
							$status  = 200;
						}
						break;
					case $this->request->is('post'):
						$request_data = $this->request->input('json_decode', true);
						if(!isset($request_data['sport_id']) OR !is_array($request_data['sport_id'])) {
							$return_data= "Sport Ids should an array";
							$success=FALSE;
							$status  = 200;
						} else if(!isset($request_data['user_id']) OR $request_data['user_id']=="") {
							$return_data= "User Id required";
							$success=FALSE;
							$status  = 200;
						} else {
							$this->SportsPreferences->deleteAll(['user_id'=>$request_data['user_id']]);
							foreach($request_data['sport_id'] as $sport_id) {
									$content_array="";
									$SportsPreferences = $this->SportsPreferences->newEntity();
									$content_array['user_id']=$request_data['user_id'];
									$content_array['sport_id']=$sport_id;
									$SportsPreferences = $this->SportsPreferences->patchEntity($SportsPreferences, $content_array);
									$this->SportsPreferences->save($SportsPreferences);
							}
								$status  = 200;
								$success = true;
								$return_data = "Data added successfully";
						}
						break;
					default:
						$status  = 400;
						$success = false;
						$return_data = "This method not allowed";
						break;
			}
		} catch (Exception $e) {
				$status  = 400;
				$return_data= json_encode(array('exception_message'=>$e->getMessage()));
				$success = false;
		}
		$this->response->type('json');
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success));
		$this->response->statusCode($status);
		$this->response->body($json);
		
	}
	
    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success('The user has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The user could not be saved. Please, try again.');
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success('The user has been deleted.');
        } else {
            $this->Flash->error('The user could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
	
	
	/**
     * Forgot Password
     */
    public function forgotpassword()
    {
		try {
			$this->layout =FALSE;
			$this->autoRender = FALSE;
			$user = $this->Users->newEntity();
			if ($this->request->is('ajax')) {
			
				
				$result = $this->Users->findByEmail($this->request->data['forgot_email'])->toArray();
				//print_r($result); die;
				if(count($result)==0) {
					$message= "Email doesn't exist";
					$success=FALSE;
				} else {
					$randomString=$this->generateRandomString();
					$this->request->data['tmp_string']=$randomString;
					$this->request->data['email']=$this->request->data['forgot_email'];
					$user = $this->Users->patchEntity($user, $this->request->data);

					$user->id=$result[0]['id'];
					if ($this->Users->save($user)) {
						$message= "Confirmation mail sent to your email.";
						$success=TRUE;
						/****** EMAIL TO USER *********/
						$register_data=array();
						$register_email=array();
						$register_email['from']=PASSWORD_RESET_MAIL_FROM;
						$register_email['from_name']=PASSWORD_RESET_MAIL_FROM_NAME;
						$register_email['bcc']=PASSWORD_RESET_MAIL_BCC;
						$register_email['to']=$this->request->data['email'];
						
						$register_data['username']=$result[0]['first_name'].' '.$result[0]['last_name'];
						$register_data['email']=$this->request->data['email'];
						$register_data['resetpassword_link']=SITE_URL."/users/newpassword/".$result[0]['id']."/".$randomString;
						$this->sendmail(PASSWORD_RESET,$register_email,$register_data,"forgot_password");
					 /****** EMAIL TO USER *********/
					 
					} else {
						
					}
				}
				$data= json_encode(array('message'=>$message,'success'=>$success));
			}
		} catch (Exception $e) {
				$data= json_encode(array('exception_message'=>$e->getMessage()));
		}
		
		echo $data;
    }
	
	public function newpassword() {
		
		if ($this->Auth->user()) {
			return $this->redirect(SITE_URL);
		}
		
		if(!isset($this->request->params['pass'][0]) OR !isset($this->request->params['pass'][1])) {
			return $this->redirect(SITE_URL);
		}
		$user_id=$this->request->params['pass'][0];
		$tmp_string=$this->request->params['pass'][1];
        $userDetails=$this->Users->find()->where(['id'=>$user_id,'tmp_string'=>$tmp_string])->first();
		$success=TRUE;
		if(count($userDetails)==0) {
			$success=FALSE;
		}
		$this->set (array('user_id'=>$user_id,'tmp_string'=>$tmp_string,'success'=>$success));	
	}
	
	/**
     * Forgot Password
     */
    public function confirmnewpassword()
    {
		try {
			$this->layout =FALSE;
			$this->autoRender = FALSE;
			if ($this->request->is('ajax')) {
				$userDetails=$this->Users->find()->where(['id'=>$this->request->data['id'],'tmp_string'=>$this->request->data['tmp_string']])->first()->toArray();
				if(count($userDetails)==0) {
					echo "User not found";
				} else {
					$user = $this->Users->newEntity();
					$this->request->data['tmp_string']='';
					$this->request->data['password']=$this->request->data['resetnew_password'];
					$user = $this->Users->patchEntity($user, $this->request->data);
					$user->id=$this->request->data['id'];
					if ($this->Users->save($user)) {
						echo "Password reset successfully, Please login with new password <a href='".SITE_URL."/carts/cartlogin'>click here</a>";
					} else {
						echo "Something might went wrong  please try again later";
					}
				}
			}
		} catch (Exception $e) {
				$data= json_encode(array('exception_message'=>$e->getMessage()));
		}
    }
	
	
	
	public function profile() {
		if ($this->Auth->user()) {	
			if ($this->request->is('ajax')) {
				$message = "Profile updated successfully";
				$this->layout =FALSE;
				$this->autoRender = FALSE;
				$user = $this->Users->newEntity();
				$this->request->data['id']=$this->Auth->user()['id'];
				
				if(isset($this->request->data['new_password']) AND ($this->request->data['new_password']!="")) {
					$this->request->data['password']=$this->request->data['new_password'];
					$message = "Password updated successfully";
				}
				$user = $this->Users->patchEntity($user, $this->request->data, ['associated' => ['Userdetails']]);
				
				if ($this->Users->save($user)) {
					$this->loadmodel('Userdetails');
					$userdata=$this->Userdetails->find('all')->where(['user_id'=>$this->Auth->user()['id']])->first();
					$userdetails = $this->Userdetails->newEntity();
					$this->request->data['id']=$userdata['id'];
					$this->request->data['user_id']=$this->Auth->user()['id'];
					$userdetails = $this->Userdetails->patchEntity($userdetails, $this->request->data);
					$this->Userdetails->save($userdetails);
				} else {
					$message= "Error while profile updating";
				}
				echo $message;
			}
			$userdata=$this->Users->find('all', ['contain' => ['Userdetails']])->where(['Users.id'=>$this->Auth->user()['id']])->first();
			$this->set (array('user_details'=>$userdata));
		} else {
			return $this->redirect(SITE_URL);
		}
	}
	
	public function orders() {
		if ($this->Auth->user()) {	
			
			$this->loadModel('Orders');
			$orderDetails=$this->Orders->find('all',['contain' => ['Orderdetails'=>['Products']]])->where(['user_id'=>$this->Auth->user('id')]);  
			$this->set (array('orderDetails'=>$orderDetails));
		} else {
			return $this->redirect(SITE_URL);
		}
	}
	
	public function measurement() {
		if ($this->Auth->user()) {	
			$this->loadModel('Measurements');
			$measurement=$this->Measurements->find('all',['contain' => ['Products']])->where(['user_id'=>$this->Auth->user('id'),'deleted'=>'0']);  
			$this->set (array('measurements'=>$measurement));
		} else {
			return $this->redirect(SITE_URL);
		}
	}
	public function removemeasurement() {
		if ($this->Auth->user()) {	
			if ($this->request->is('ajax')) {
				$this->layout =FALSE;
				$this->autoRender = FALSE;
					$this->loadModel('Measurements');
					$measurements = $this->Measurements->newEntity();
					$this->request->data['deleted']=1;
					$userdetails = $this->Measurements->patchEntity($measurements, $this->request->data);
					$data=$this->Measurements->save($userdetails);
				
			}
		} else {
			return $this->redirect(SITE_URL);
		}
	}
	
	
}
