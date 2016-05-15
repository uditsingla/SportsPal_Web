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
										foreach($return_data['sports_preferences'] as $sports_preferences) {
											$sportids[]=$sports_preferences['sport_id'];
										}
										$whr=[];
										$whr['Games.sport_id IN']=$sportids;
										if(isset($search_keyword) && $search_keyword!='') {
											$whr['Games.name LIKE']='%'.$search_keyword.'%';
										}
										$allGames = $this->Games->find('all',['contain' => ['Users', 'Sports']])->select(['Games.id','Games.name','Games.sport_id','Games.user_id','Games.game_type','Games.team_id','Games.date','Games.time','Games.latitude','Games.longitude','Games.address','Games.modified','Games.created','Users.first_name','Users.last_name','Users.email','Sports.name'])->where($whr);
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
									if(isset($request_data['is_nearby']) AND ($request_data['is_nearby'])==1) {
										$allGamesSet=[];
										foreach($allGames as $allGame) {
											
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
										$allGames=$allGamesSet;
									}
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
}