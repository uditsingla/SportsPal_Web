<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Sports Controller
 *
 * @property \App\Model\Table\SportsTable $Sports
 */
class SportsController extends AppController
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
			$this->loadmodel('Sports');		
			switch (true) {
					case $this->request->is('get'):
						$return_data= $this->Sports->find('all')->order(['name'=>'asc']);
						$success=TRUE;
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
}
