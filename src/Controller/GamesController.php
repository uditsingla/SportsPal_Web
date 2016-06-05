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
										
										$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports', 'Teams']])->select(['Games.id','Games.name','Games.sport_id','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Games.game_status','Games.member_limit','Users.first_name','Users.last_name','Users.email','Users.image','Sports.name','Teams.team_name'])->where($whr)->order(['Games.id' => 'DESC']);
										
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
										$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.sport_id','Games.name','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Games.game_status','Games.member_limit','Users.first_name','Users.last_name','Users.email','Users.image','Sports.name'])->order(['Games.id' => 'DESC']);;
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
									$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.name','Games.sport_id','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Games.game_status','Games.member_limit','Users.first_name','Users.last_name','Users.email','Users.image','Sports.name'])->where($whr)->order(['Games.id' => 'DESC']);
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
								$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.sport_id','Games.name','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Games.game_status','Games.member_limit','Users.first_name','Users.last_name','Users.email','Users.image','Sports.name'])->order(['Games.id' => 'DESC']);
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
			$this->loadmodel('GameMembers');
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
											
											$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports', 'GameMembers']])->select()->where(['Games.id'=>$game_id])->first();
											
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
										$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports', 'GameMembers']])->select()->where(['Games.id'=>$game_id])->first();
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
								
									$allChallenge = $this->GameChallenges->find('all',['contain' => ['Teams', 'Users']])->where(['GameChallenges.game_id'=>$game_id])->order(['GameChallenges.id' => 'DESC']);
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
									$content_array['team_id']=(isset($request_data['team_id'])?$request_data['team_id']:'0');
									if($challengeDetails['game_status']=='open') {
										$content_array['status']="1";
									} else {
										$content_array['status']="0";
									}
									
									
									$TeamChallenges = $this->GameChallenges->patchEntity($GameChallenges, $content_array);
									$ifChanllenge = $this->GameChallenges->save($GameChallenges);
									if($ifChanllenge) {
										$status  = 200;
										$success = true;
										$return_data = "Challenge set";	
										$data['challenge_id'] = $ifChanllenge->id;	
										
										$this->Notifications->notifyGameChallenge($challengeDetails['user_id'],$game_id,$ifChanllenge->id,$request_data['user_id']);
										
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
									$this->Notifications->GameChallengeStatus($challengeDetails['user_id'],$challengeDetails['game_id'],$request_data['challenge_id'],'rejected');
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
										$this->Notifications->GameChallengeStatus($challengeDetails['user_id'],$challengeDetails['game_id'],$ifChanllenge->id,'accepted');
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
	public function users($user_id=null){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Games');
			$this->loadmodel('GameChallenges');
			$data="";			
			switch (true) {
						case $this->request->is('get'):
								if($user_id!='') {
								
									$allChallenge = $this->Games->find('all',['contain' => ['GameChallenges'=>['Teams', 'Users'],'Sports','Teams']])->where(['Games.user_id'=>$user_id])->order(['Games.id' => 'DESC']);
									
									$success = true;
								} else {
									$allChallenge="User id required";
									$success = false;
								}
								
								$status  = 200;
								$return_data = $allChallenge;	
								
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
	
	
	public function member($game_id){
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Games');
			$this->loadmodel('GameMembers');
			$data="";			
			switch (true) {
							case $this->request->is('post'):
								if($game_id!='') {
									$request_data = $this->request->input('json_decode', true); 
									$return_data= $this->Games->find('all')->select()->where(['Games.id'=>$game_id])->first();
									if($return_data) {
										$game_status=$return_data['game_status'];
										$game_type=$return_data['game_type'];
										$game_owner=$return_data['user_id'];
										$member_limit=$return_data['member_limit'];
										if($game_type=='individual') {
											$total_members= $this->GameMembers->find('all')->select()->where(['GameMembers.game_id'=>$game_id,'status'=>1]);
												if(count($total_members->toArray())<$member_limit) {
													$GameMembers = $this->GameMembers->newEntity();
													$content_array['game_id']=$game_id;
													if(isset($request_data['user_id']) AND $request_data['user_id']!='') {
														$content_array['user_id']=$request_data['user_id'];
														if($game_status=='open') { 
															$content_array['status']=1;
														} else {
															$content_array['status']=0;
														}
														$GameMembers = $this->GameMembers->patchEntity($GameMembers, $content_array);
														$ifMember = $this->GameMembers->save($GameMembers);
														if($ifMember) {
																$return_data="Member added succesfully";
																$success = true;
																
															$this->Notifications->notifyGameOwner($game_owner,$game_id,$request_data['user_id'],$content_array['status'],$ifMember->id);
																
														} else {
															$return_data="Error while adding member";
															$success = false;
														}
														
													} else {
														$return_data="User id required";
														$success = false;
													}
												} else {
													$return_data="Members limit reached";
													$success = false;
												}
										} else {
											$return_data="Game type is team";
											$success = false;
										}
									} else {
										$return_data="Game not found";
										$success = false;
									}
								} else {
									$return_data="Game id required";
									$success = false;
								}
								$status  = 200;
							break;
						case $this->request->is('get'):
								if($game_id!='') {
									$return_data= $this->GameMembers->find('all',['contain'=>['Users']])->select()->where(['GameMembers.game_id'=>$game_id]);
									$success = true;
								} else {
									$return_data="Game id required";
									$success = false;
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
		$json = json_encode(array('status'=>$status,'message'=>$return_data,'success'=>$success,'data'=>$data));
		$this->response->statusCode($status);
		$this->response->body($json);
		
	}
	
	public function memberstatus($game_id){
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Games');
			$this->loadmodel('GameMembers');
			$data="";			
			switch (true) {
							case $this->request->is('post'):
								if($game_id!='') {
									$request_data = $this->request->input('json_decode', true); 
									$return_data= $this->Games->find('all')->select()->where(['Games.id'=>$game_id])->first();
									if($return_data) {
										$game_status=$return_data['game_status'];
										$game_type=$return_data['game_type'];
										$member_limit=$return_data['member_limit'];
										$game_owner=$return_data['user_id'];
										if($game_type=='individual') {
											$total_members= $this->GameMembers->find('all')->select()->where(['GameMembers.game_id'=>$game_id,'status'=>1]);
												if(count($total_members->toArray())<$member_limit) {
													if(isset($request_data['request_id']) AND $request_data['request_id']!='') {
													 $requestData = $this->GameMembers->find('all')->select()->where(['GameMembers.id'=>$request_data['request_id']])->first();	
														if($requestData) {
															$GameMembers = $this->GameMembers->newEntity();
															$content_array['game_id']=$game_id;
															$content_array['id']=$request_data['request_id'];
															$content_array['status']=1;
															$GameMembers = $this->GameMembers->patchEntity($GameMembers, $content_array);
															$ifMember = $this->GameMembers->save($GameMembers);
															$return_data="Member status changed succesfully";
															$success = true;
															
															$this->Notifications->notifyGameMember($requestData['user_id'],$game_id,$game_owner,1);
														} else {
															$return_data="Request not found";
															$success = false;
														}
													} else {
														$return_data="Request id required";
														$success = false;
													}
												} else {
													$return_data="Members limit reached";
													$success = false;
												}
										} else {
											$return_data="Game type is team";
											$success = false;
										}
									} else {
										$return_data="Game not found";
										$success = false;
									}
								} else {
									$return_data="Game id required";
									$success = false;
								}
								$status  = 200;
							break;
						case $this->request->is('delete'):
								if($game_id!='') {
									$return_data= $this->Games->find('all')->select()->where(['Games.id'=>$game_id])->first();
									if($return_data) {
										$game_owner=$return_data['user_id'];
										$request_data = $this->request->input('json_decode', true); 
										if(isset($request_data['request_id']) AND $request_data['request_id']!='') {
											$requestData = $this->GameMembers->find('all')->select()->where(['GameMembers.id'=>$request_data['request_id']])->first();	
										if($requestData) {				
											$return_data= $this->GameMembers->deleteAll(['GameMembers.id'=>$request_data['request_id']]);
											if($return_data) {
												$return_data="Request deleted successfully";
												$success = true;
												$this->Notifications->notifyGameMember($requestData['user_id'],$game_id,$game_owner,0);
											} else {
												$return_data="Error while deleting request";
												$success = false;
											}
										} else {
											$return_data="Request not found";
											$success = false;
										}
									  } else {
										$return_data="Request id required";
										$success = false;
									  }
								 } else {
									$return_data="Game not found";
									$success = false;
								  }
								} else {
									$return_data="Game id required";
									$success = false;
								}
								$status  = 200;
							break;
						default:
							$status  = 200;
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
	
	public function memberrequests($user_id){
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Games');
			$this->loadmodel('GameMembers');
			$data="";			
			switch (true) {
							case $this->request->is('get'):
								if($user_id!='') {
								
									$allRequests = $this->Games->find('all',['contain' => ['GameMembers'=>['Users'],'Sports']])->where(['Games.user_id'=>$user_id])->order(['Games.id' => 'DESC']);
									
									$success = true;
								} else {
									$allRequests="User id required";
									$success = false;
								}
								
								$status  = 200;
								$return_data = $allRequests;	
								
							break;
						default:
							$status  = 200;
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
