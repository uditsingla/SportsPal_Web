<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Teams Controller
 *
 * @property \App\Model\Table\GamesTable $Sports
 */
class TeamsController extends AppController
{

	public function initialize()
    {
		parent::initialize();
		$this->loadComponent('RequestHandler');
		//$this->RequestHandler->config('inputTypeMap.json', ['json_decode', true]);
   
	}
    
	public function beforeFilter(\Cake\Event\Event $event)
	{
		//$this->Auth->allow(['login','add','logout','forgotpassword','newpassword','confirmpassword']);
		parent::beforeFilter($event);		
	}

	
	public function index($team_id=null){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Teams');
			$this->loadmodel('TeamMembers');
			$Teams = $this->Teams->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
								if(isset($team_id) && $team_id!=null) {
									$allTeams = $this->Teams->find('all',['contain' => ['Users', 'Sports','TeamMembers'=>['Users']]])->select(['Teams.id','Teams.sport_id','Teams.team_name','Teams.team_type','Teams.members_limit','Teams.latitude','Teams.longitude','Teams.address','Teams.creator_id','Teams.created','Teams.modified','Users.first_name','Users.last_name','Users.email','Sports.name'])->where(['Teams.id'=>$team_id])->first();
								} else {
									$allTeams = $this->Teams->find('all',['contain' => ['Users', 'Sports']])->select(['Teams.id','Teams.sport_id','Teams.team_name','Teams.team_type','Teams.members_limit','Teams.latitude','Teams.longitude','Teams.address','Teams.creator_id','Teams.created','Teams.modified','Users.first_name','Users.last_name','Users.email','Sports.name']);
								}
								$status  = 200;
								$success = true;
								$return_data = $allTeams;	
								
							break;
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($request_data['sport_id']) OR !isset($request_data['team_name']) OR !isset($request_data['team_type']) OR !isset($request_data['members_limit']) OR !isset($request_data['latitude']) OR !isset($request_data['longitude']) OR !isset($request_data['address']) OR !isset($request_data['creator_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else { 
								if(isset($request_data['team_members'])) {
									if(is_array($request_data['team_members'])) {
										$team_members = $request_data['team_members'];
									}
									unset($request_data['team_members']);
								}
								$Teams = $this->Teams->patchEntity($Teams, $request_data);
								$checkIf=$this->Teams->save($Teams);
								if($checkIf) {
									
									foreach($team_members as $member_id) {
										$content_array="";
										$TeamMembers = $this->TeamMembers->newEntity();
										$content_array['user_id']=$member_id;
										$content_array['team_id']=$checkIf->id;
										$TeamMembers = $this->TeamMembers->patchEntity($TeamMembers, $content_array);
										$this->TeamMembers->save($TeamMembers);
									}
							
									$status  = 200;
									$success = true;
									$return_data = "Team created successfully";	
									$data = array("team_id"=>$checkIf->id);	
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
				$status  = 400;
				$return_data= json_encode(array('exception_message'=>$e->getMessage()));
				$success = false;
		}
		$this->response->type('json');
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success,'data'=>$data));
		$this->response->statusCode($status);
		$this->response->body($json);
		
	}
	
	
	public function search(){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Teams');
			$Teams = $this->Teams->newEntity();		
					switch (true) {
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
							$whr='';
							if(isset($request_data['sport_id'])) {
								$whr['Sports.id']=$request_data['sport_id'];  
							}
							if(isset($request_data['creator_id'])) {
								$whr['Users.id']=$request_data['creator_id'];
							}
							
							$allGames = $this->Teams->find('all',['contain' => ['Users', 'Sports']])->select(['Teams.id','Teams.sport_id','Teams.team_name','Teams.team_type','Teams.members_limit','Teams.latitude','Teams.longitude','Teams.address','Teams.creator_id','Teams.created','Teams.modified','Users.first_name','Users.last_name','Users.email','Sports.name'])->where($whr);
								$status  = 200;
								$success = true;
								$return_data = $allGames;	
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
	
	public function join(){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Teams');
			$this->loadmodel('TeamMembers');
			$Teams = $this->Teams->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($request_data['team_id']) OR !isset($request_data['user_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
									
								 $memberDetails=$this->TeamMembers->find()->where(['team_id'=>$request_data['team_id'],'user_id'=>$request_data['user_id']])->first();
								if(count($memberDetails)==0) {
									$content_array="";
									$TeamMembers = $this->TeamMembers->newEntity();
									$content_array['user_id']=$request_data['user_id'];
									$content_array['team_id']=$request_data['team_id'];
									if(isset($request_data['status'])) { 
										$content_array['status']=$request_data['status']; 
									} else { 
										$content_array['status'] = '0'; 
									}
									$TeamMembers = $this->TeamMembers->patchEntity($TeamMembers, $content_array);
									$this->TeamMembers->save($TeamMembers);
									
									$status  = 200;
									$success = true;
									$return_data = "User added in team";
										
								} else {
										$status  = 200;
										$success = false;
										$return_data = "User already exist in this team";	
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
				$status  = 400;
				$return_data= json_encode(array('exception_message'=>$e->getMessage()));
				$success = false;
		}
		$this->response->type('json');
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success));
		$this->response->statusCode($status);
		$this->response->body($json);
		
	}
	
	public function request($team_id){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Teams');
			$this->loadmodel('TeamMembers');
			$Teams = $this->Teams->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
							
								$allTeamMembers = $this->TeamMembers->find('all',['contain' => ['Users']])->select(['TeamMembers.id','TeamMembers.team_id','TeamMembers.user_id','TeamMembers.status','Users.first_name','Users.last_name','Users.email'])->where(['TeamMembers.team_id'=>$team_id,'TeamMembers.status'=>'0']);
								$status  = 200;
								$success = true;
								$return_data = $allTeamMembers;	
								
							break;
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($team_id) OR !isset($request_data['request_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
									
								 $memberDetails=$this->TeamMembers->find()->where(['id'=>$request_data['request_id']])->first();
								if(count($memberDetails)==0) {
									$status  = 200;
									$success = false;
									$return_data = "Request not found";	
										
								} else {
										$content_array="";
									$TeamMembers = $this->TeamMembers->newEntity();
									$content_array['id']=$request_data['request_id'];
									$content_array['team_id']=$team_id;
									if(isset($request_data['status'])) { 
										$content_array['status']=$request_data['status']; 
									} else { 
										$content_array['status'] = '0'; 
									}
									$TeamMembers = $this->TeamMembers->patchEntity($TeamMembers, $content_array);
									$this->TeamMembers->save($TeamMembers);
									
									$status  = 200;
									$success = true;
									$return_data = "User status changed in team";	
								}
							}
							break;
						case $this->request->is('delete'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($team_id) OR !isset($request_data['request_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
									
								 $memberDetails=$this->TeamMembers->find()->where(['id'=>$request_data['request_id']])->first();
								if(count($memberDetails)==0) {
									$status  = 200;
									$success = false;
									$return_data = "Request not found";	
										
								} else {
									$this->TeamMembers->deleteAll(['id'=>$request_data['request_id']]);
									$status  = 200;
									$success = true;
									$return_data = "Request deleted successfully";	
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
				$status  = 400;
				$return_data= json_encode(array('exception_message'=>$e->getMessage()));
				$success = false;
		}
		$this->response->type('json');
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success));
		$this->response->statusCode($status);
		$this->response->body($json);
		
	}
	
	public function challenge($team_id=null,$user_id=null){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Teams');
			$this->loadmodel('TeamMembers');
			$this->loadmodel('TeamChallenges');
			$Teams = $this->Teams->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
							
								$allChallenge = $this->TeamChallenges->find('all')->where(['TeamChallenges.team2_id'=>$team_id]);
								$status  = 200;
								$success = true;
								$return_data = $allChallenge;	
								
							break;
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true); 
							if(!isset($team_id) OR empty($team_id) OR !isset($user_id) OR empty($user_id) OR !isset($request_data['challenge_to']) OR empty($request_data['challenge_to'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
									
									$content_array="";
									$TeamChallenges = $this->TeamChallenges->newEntity();
									
									$content_array['user_id']=$user_id;
									$content_array['team1_id']=$team_id;
									$content_array['team2_id']=$request_data['challenge_to'];
									$content_array['status']="0";
									
									$TeamChallenges = $this->TeamChallenges->patchEntity($TeamChallenges, $content_array);
									$ifChanllenge = $this->TeamChallenges->save($TeamChallenges);
									if($ifChanllenge) {
										$status  = 200;
										$success = true;
										$return_data = "Challenge sent to team";	
										$data['challenge_id'] = $ifChanllenge->id;	
									} else {
										$status  = 200;
										$success = false;
										$return_data = "Error";
									}
							}
							break;
						case $this->request->is('delete'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($request_data['challenge_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
									
								 $challengeDetails=$this->TeamChallenges->find()->where(['id'=>$request_data['challenge_id']])->first();
								if(count($challengeDetails)==0) {
									$status  = 200;
									$success = false;
									$return_data = "Request not found";	
										
								} else {
									$this->TeamChallenges->deleteAll(['id'=>$request_data['challenge_id']]);
									$status  = 200;
									$success = true;
									$return_data = "Challenge deleted successfully";	
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
				$status  = 400;
				$return_data= json_encode(array('exception_message'=>$e->getMessage()));
				$success = false;
		}
		$this->response->type('json');
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success,'data'=>$data));
		$this->response->statusCode($status);
		$this->response->body($json);
		
	}
	
}
