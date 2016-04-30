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

	
	public function index(){
		
		try {
			$this->autoRender = FALSE;	
			$this->loadmodel('Teams');
			$Teams = $this->Teams->newEntity();
			$data="";			
			switch (true) {
						case $this->request->is('get'):
							
								$allTeams = $this->Teams->find('all',['contain' => ['Users', 'Sports']])->select(['Teams.id','Teams.sport_id','Teams.team_name','Teams.team_type','Teams.members_limit','Teams.latitude','Teams.longitude','Teams.address','Teams.creator_id','Teams.created','Teams.modified','Users.first_name','Users.last_name','Users.email','Sports.name']);
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
								$Teams = $this->Teams->patchEntity($Teams, $request_data);
								$checkIf=$this->Teams->save($Teams);
								if($checkIf) {
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
}
