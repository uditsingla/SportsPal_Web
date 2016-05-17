<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Games Controller
 *
 * @property \App\Model\Table\GamesTable $Sports
 */
class GamesController extends AppController
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

	
	public function index($user_id='',$search_keyword=''){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Games');
			$this->loadmodel('Users');
			$Games = $this->Games->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
							if($user_id!="") {
								$return_data= $this->Users->find('all',['contain' => ['SportsPreferences']])->select()->where(['Users.id'=>$user_id])->first();
									if($return_data) {
										$sportids='';
										$mainUserlat=$return_data->latitude;
										$mainUserlong=$return_data->longitude;
										foreach($return_data['sports_preferences'] as $sports_preferences) {
											$sportids[]=$sports_preferences['sport_id'];
										}
										$whr=[];
										$whr['Games.sport_id IN']=$sportids;
										if(isset($search_keyword) && $search_keyword!='') {
											$whr['Games.name LIKE']='%'.$search_keyword.'%';
										}
										$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.name','Games.sport_id','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Users.first_name','Users.last_name','Users.email','Sports.name'])->where($whr);
										$allGamesSet=array();
										foreach($allGames as $allGame) {
											
											if(isset($allGame['latitude']) && isset($allGame['longitude']) && !empty($allGame['longitude']) && !empty($allGame['longitude'])) {
												$allGame['distance']=$this->distance($mainUserlat,$mainUserlong,$allGame['latitude'],$allGame['longitude']);
												
											} 
											$allGamesSet[]=$allGame;
										}
										$allGames = $allGamesSet;
										$success = true;
									} else {
										$allGames="User not found";
										$success=FALSE;
										$status  = 200;
									}
								
									
									} else {
										$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.sport_id','Games.name','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Users.first_name','Users.last_name','Users.email','Sports.name']);
										$success = true;
									}
							
								$status  = 200;
								$return_data = $allGames;	
							break;
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
							if(!isset($request_data['sport_id']) OR !isset($request_data['user_id']) OR !isset($request_data['game_type']) OR !isset($request_data['date']) OR !isset($request_data['time']) OR !isset($request_data['latitude']) OR !isset($request_data['longitude']) OR !isset($request_data['address'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
								$Games = $this->Games->patchEntity($Games, $request_data);
								$checkIf=$this->Games->save($Games);
								if($checkIf) {
									$status  = 200;
									$success = true;
									$return_data = "Game created successfully";	
									$data = array("game_id"=>$checkIf->id);	
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
			$this->loadmodel('Games');
			$this->loadmodel('Users');
			$Games = $this->Games->newEntity();		
					switch (true) {
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true);
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
										$whr['Games.sport_id IN']=$sportids;
									}
									if(isset($request_data['sports_id']) AND ($request_data['sports_id'])!='') {
										$whr['Games.sport_id']=$request_data['sports_id'];
									}		
									if(isset($request_data['keyword']) AND ($request_data['keyword'])!='' AND ($request_data['is_keyword'])!='' AND ($request_data['is_keyword'])==1) {
										$whr['Games.name LIKE']='%'.$request_data['keyword'].'%';
									}
									if(isset($request_data['is_creator']) AND ($request_data['is_creator'])==1) {
										$whr['Games.user_id']=$request_data['user_id'];
									}
									$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.name','Games.sport_id','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Users.first_name','Users.last_name','Users.email','Sports.name'])->where($whr);
									/******************************************* Get NearBy *********************************/
									
										$allGamesSet=[];
										foreach($allGames as $allGame) {
											
											if(isset($allGame['latitude']) && isset($allGame['longitude']) && !empty($allGame['longitude']) && !empty($allGame['longitude'])) {
												$allGame['distance']=$this->distance($mainUserlat,$mainUserlong,$allGame['latitude'],$allGame['longitude']);
												if($allGame['distance']>50 AND isset($request_data['is_nearby']) AND ($request_data['is_nearby'])==1) {
													continue;
												}
											} else {
												$allGame['distance']=0;
											}
											$allGamesSet[]=$allGame;
										}
										$allGames=$allGamesSet;
									
								/******************************************* End get NearBy ************************************/
									$success = true;
								} else {
									$allGames="User not found";
									$success=FALSE;
								}
								
							} else {
								$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.sport_id','Games.name','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Users.first_name','Users.last_name','Users.email','Sports.name']);
								$success = true;
							}
								$status  = 200;
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
	
	
	public function singlegame($game_id,$user_id=''){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Games');
			$this->loadmodel('Users');
			$Games = $this->Games->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
								if(isset($game_id) && $game_id!=null) {
									if($user_id!="") {
										$return_data= $this->Users->find('all',['contain' => ['SportsPreferences']])->select()->where(['Users.id'=>$user_id])->first();
										if($return_data) {
											$sportids='';
											$mainUserlat=$return_data->latitude;
											$mainUserlong=$return_data->longitude;
											
											$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.name','Games.sport_id','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Users.first_name','Users.last_name','Users.email','Sports.name'])->where(['Games.id'=>$game_id])->first();
											
											if(count($allGames)>0) {
												if(isset($allGames['latitude']) && isset($allGames['longitude']) && !empty($allGames['longitude']) && !empty($allGames['longitude'])) {
													$allGames['distance']=$this->distance($mainUserlat,$mainUserlong,$allGames['latitude'],$allGames['longitude']);
													
												} 
											} else {
												$allGames=array();
											}
											
											
											$success = true;
										} else {
											$allGames="User not found";
											$success=FALSE;
										}
									} else {
										$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.sport_id','Games.name','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Users.first_name','Users.last_name','Users.email','Sports.name'])->where(['Games.id'=>$game_id])->first();
										$success = true;
									}
									
									$status  = 200;
									$return_data = $allGames;	
								} else {
									$status  = 200;
									$success = false;
									$return_data = "Game Id required";	
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
	
	
	public function challenge($game_id=null){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Games');
			$this->loadmodel('GameChallenges');
			$Games = $this->Games->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
								if($game_id!='') {
								
									$allChallenge = $this->GameChallenges->find('all')->where(['GameChallenges.game_id'=>$game_id]);
									$success = true;
								} else {
									$allChallenge="Game id required";
									$success = false;
								}
								
								$status  = 200;
								$return_data = $allChallenge;	
								
							break;
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true); 
							if(!isset($game_id) OR empty($game_id) OR !isset($request_data['user_id']) OR empty($request_data['user_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
								$challengeDetails=$this->Games->find()->where(['id'=>$game_id])->first();
								if(count($challengeDetails)==0) {
									$status  = 200;
									$success = false;
									$return_data = "Game not found";	
										
								} else {
									$content_array="";
									$GameChallenges = $this->GameChallenges->newEntity();
									$content_array['game_id']=$game_id;
									$content_array['user_id']=$request_data['user_id'];
									$content_array['team_id']=(isset($request_data['challenge_to'])?$request_data['challenge_to']:'0');
									$content_array['status']="0";
									
									$TeamChallenges = $this->GameChallenges->patchEntity($GameChallenges, $content_array);
									$ifChanllenge = $this->GameChallenges->save($GameChallenges);
									if($ifChanllenge) {
										$status  = 200;
										$success = true;
										$return_data = "Challenge set";	
										$data['challenge_id'] = $ifChanllenge->id;	
									} else {
										$status  = 200;
										$success = false;
										$return_data = "Error";
									}
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
									
								 $challengeDetails=$this->GameChallenges->find()->where(['id'=>$request_data['challenge_id']])->first();
								if(count($challengeDetails)==0) {
									$status  = 200;
									$success = false;
									$return_data = "Request not found";	
										
								} else {
									$this->GameChallenges->deleteAll(['id'=>$request_data['challenge_id']]);
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
	
	
	public function acceptchallenge(){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Games');
			$this->loadmodel('GameChallenges');
			$Games = $this->Games->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('post'):
							$request_data = $this->request->input('json_decode', true); 
							if(!isset($request_data['challenge_id']) OR empty($request_data['challenge_id']) OR !isset($request_data['user_id']) OR empty($request_data['user_id'])) {
								$status  = 200;
								$success = false;
								$return_data = "Data Missing";
							} else {
								$challengeDetails=$this->GameChallenges->find()->where(['id'=>$request_data['challenge_id']])->first();
								if(count($challengeDetails)==0) {
									$status  = 200;
									$success = false;
									$return_data = "Game not found";	
										
								} else {
									$content_array="";
									$GameChallenges = $this->GameChallenges->newEntity();
									$content_array['id']=$request_data['challenge_id'];
									$content_array['status']="1";
									
									$TeamChallenges = $this->GameChallenges->patchEntity($GameChallenges, $content_array);
									$ifChanllenge = $this->GameChallenges->save($GameChallenges);
									if($ifChanllenge) {
										$status  = 200;
										$success = true;
										$return_data = "Challenge accepted";	
										$data['challenge_id'] = $ifChanllenge->id;	
									} else {
										$status  = 200;
										$success = false;
										$return_data = "Error";
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
