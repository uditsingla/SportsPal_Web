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
		$this->loadComponent('Notifications');
   
	}
    
	public function beforeFilter(\Cake\Event\Event $event)
	{
		//$this->Auth->allow(['login','add','logout','forgotpassword','newpassword','confirmpassword']);
		parent::beforeFilter($event);		
	}

	
	public function index($user_id='',$search_keyword=''){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Teams');
			$this->loadmodel('TeamMembers');
			$this->loadmodel('Users');
			$Teams = $this->Teams->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
								if($user_id!="") {
								$return_data= $this->Users->find('all',['contain' => ['SportsPreferences']])->select()->where(['Users.id'=>$user_id])->first();
								if($return_data) {
									$sportids='';
									foreach($return_data['sports_preferences'] as $sports_preferences) {
										$sportids[]=$sports_preferences['sport_id'];
									}
									$whr=[];
									$whr['Teams.sport_id IN']=$sportids;
									if(isset($search_keyword) && $search_keyword!='') {
										$whr['Teams.team_name LIKE']='%'.$search_keyword.'%';
									}
									$allTeams = $this->Teams->find('all',['contain' => ['Users', 'Sports']])->select(['Teams.id','Teams.sport_id','Teams.team_name','Teams.team_type','Teams.members_limit','Teams.latitude','Teams.longitude','Teams.address','Teams.creator_id','Teams.created','Teams.modified','Users.first_name','Users.last_name','Users.email','Sports.name'])->where($whr);
									$success = true;
								} else {
									$allTeams="User not found";
									$success=FALSE;
								}
							
								
							} else {
								$allTeams = $this->Teams->find('all',['contain' => ['Users', 'Sports']])->select(['Teams.id','Teams.sport_id','Teams.team_name','Teams.team_type','Teams.members_limit','Teams.latitude','Teams.longitude','Teams.address','Teams.creator_id','Teams.created','Teams.modified','Users.first_name','Users.last_name','Users.email','Sports.name']);
								$success = true;
							}
							
								$status  = 200;
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
										$content_array['status']='0';
										$TeamMembers = $this->TeamMembers->patchEntity($TeamMembers, $content_array);
										$this->TeamMembers->save($TeamMembers);
										$this->Notifications->notifyMembers($member_id,$checkIf->id,$request_data['creator_id']);
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
	
	public function singleteam($team_id){
		
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
									$status  = 200;
									$success = true;
									$return_data = $allTeams;	
								} else {
									$status  = 200;
									$success = false;
									$return_data = "Team Id required";	
								}
								
								
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
			$this->loadmodel('Users');
			$Teams = $this->Teams->newEntity();		
					switch (true) {
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
							$whr='';
							if($request_data['user_id']!="") {
								$return_data= $this->Users->find('all',['contain' => ['SportsPreferences']])->select()->where(['Users.id'=>$request_data['user_id']])->first();
								if($return_data) {
									$mainUserlat=$return_data->latitude;
									$mainUserlong=$return_data->longitude;
									$sportids='';
									foreach($return_data['sports_preferences'] as $sports_preferences) {
										$sportids[]=$sports_preferences['sport_id'];
									}
									$whr=[];
									
									if(isset($request_data['is_preferred']) AND ($request_data['is_preferred'])==1) {
										$whr['Teams.sport_id IN']=$sportids;
									}
									if(isset($request_data['sports_id']) AND ($request_data['sports_id'])!='') {
										$whr['Teams.sport_id']=$request_data['sports_id'];
									}		
									if(isset($request_data['keyword']) AND ($request_data['keyword'])!='' AND ($request_data['is_keyword'])!='' AND ($request_data['is_keyword'])==1) {
										$whr['Teams.team_name LIKE']='%'.$request_data['keyword'].'%';
									}
									if(isset($request_data['is_creator']) AND ($request_data['is_creator'])==1) {
										$whr['Teams.creator_id']=$request_data['user_id'];
									}
									$return_data = $this->Teams->find('all',['contain' => ['Users', 'Sports']])->select(['Teams.id','Teams.sport_id','Teams.team_name','Teams.team_type','Teams.members_limit','Teams.latitude','Teams.longitude','Teams.address','Teams.creator_id','Teams.created','Teams.modified','Users.first_name','Users.last_name','Users.email','Sports.name'])->where($whr);
									/******************************************* Get NearBy *********************************/
									if(isset($request_data['is_nearby']) AND ($request_data['is_nearby'])==1) {
										$allGamesSet=[];
										foreach($return_data as $allGame) {
											
											if(isset($allGame['latitude']) && isset($allGame['longitude']) && !empty($allGame['longitude']) && !empty($allGame['longitude'])) {
												$allGame['distance']=$this->distance($mainUserlat,$mainUserlong,$allGame['latitude'],$allGame['longitude']);
												if($allGame['distance']>50) {
													continue;
												}
											} else {
												$allGame['distance']=0;
											}
											$allGamesSet[]=$allGame;
										}
										$return_data=$allGamesSet;
									}
								/******************************************* End get NearBy ************************************/
									$success = true;
								} else {
									$return_data="User not found";
									$success=FALSE;
								}
								
							}
								$status  = 200;	
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
	
	
	public function userTeamRequest($user_id){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Teams');
			$this->loadmodel('TeamMembers');
			$Teams = $this->Teams->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
							
								$allTeamMembers = $this->TeamMembers->find('all',['contain' => ['Teams'=>['Users','Sports']]])->autoFields(true)->select(['TeamMembers.id','TeamMembers.team_id','TeamMembers.user_id','TeamMembers.status','Teams.team_name'])->where(['TeamMembers.user_id'=>$user_id,'TeamMembers.status'=>'0']);
								
								$status  = 200;
								$success = true;
								$return_data = $allTeamMembers;	
								
							break;
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($user_id) OR !isset($request_data['request_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
									
								 $memberDetails=$this->TeamMembers->find()->where(['id'=>$request_data['request_id'],'team_id'=>$request_data['team_id'],'user_id'=>$user_id,'status'=>'0'])->first();
								if(count($memberDetails)==0) {
									$status  = 200;
									$success = false;
									$return_data = "Request not found";	
										
								} else {
									$content_array="";
									$TeamMembers = $this->TeamMembers->newEntity();
									$content_array['id']=$request_data['request_id'];
									$content_array['team_id']=$request_data['team_id'];
									$content_array['user_id']=$user_id;
									$content_array['status'] = '1'; 

									$TeamMembers = $this->TeamMembers->patchEntity($TeamMembers, $content_array);
									$this->TeamMembers->save($TeamMembers);
									
									/************************ Notifications **************************************/
										
										$teamDetails=$this->Teams->find()->where(['id'=>$request_data['team_id']])->first();
										if(count($teamDetails)!=0) {
											$this->Notifications->notifyAdmin($teamDetails['creator_id'],$request_data['team_id'],$user_id,'accepted');
										}
										
									/************************ End ***************************/
									
									$status  = 200;
									$success = true;
									$return_data = "User status changed in team";	
								}
							}
							break;
						case $this->request->is('delete'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($user_id) OR !isset($request_data['request_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
									
								 $memberDetails=$this->TeamMembers->find()->where(['id'=>$request_data['request_id'],'user_id'=>$user_id,'team_id'=>$request_data['team_id']])->first();
								if(count($memberDetails)==0) {
									$status  = 200;
									$success = false;
									$return_data = "Request not found";	
										
								} else {
									$this->TeamMembers->deleteAll(['id'=>$request_data['request_id'],'user_id'=>$user_id,'team_id'=>$request_data['team_id']]);
									$status  = 200;
									$success = true;
									$return_data = "Request deleted successfully";	
									
									/************************ Notifications **************************************/
										
										$teamDetails=$this->Teams->find()->where(['id'=>$request_data['team_id']])->first();
										if(count($teamDetails)!=0) {
											$this->Notifications->notifyAdmin($teamDetails['creator_id'],$request_data['team_id'],$user_id,'rejected');
										}
										
									/************************ End ***************************/
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
	
	
}
