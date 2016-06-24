<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Core\Exception\Exception;
use Cake\Network\Exception\HttpException;
use Cake\Network\Exception\BadRequestException;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */

    ////// add by sahoo
    public $session_user_id;
    public $session_user_first_name;
    public $session_user_last_name;
    public $pubnub;

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
		
		 $this->loadComponent('Auth', [
				'authenticate' => [
				'Form' => [
						'fields' => [
							'username' => 'email',
							'password' => 'password'
						]
					]
				 ],
				'loginAction' => [
						'controller' => 'Users',
						'action' => 'login'
					],
					'authorize' => 'Controller',
					'unauthorizedRedirect' => $this->referer()
				]);
		
		if($this->Auth->user()) {
			$this->set('authUser', $this->Auth->user());
		}
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }
	
	function beforeFilter(Event $event)
	{
		if (!in_array($this->request->params['action'],array('login', 'add','register','Pushnotifications','retrieveNotifications','forgotPassword','resetPassword','verifyResetPassToken'))) {
			
			if ($this->request->header('username') && $this->request->header('usertoken')) {
					$authenticate = $this->userAuthenticate($this->request->header('username'), $this->request->header('usertoken'));
					if (!$authenticate) {
						throw new BadRequestException("Data Missing");
					}
			} else {
				throw new BadRequestException("Data Missing");
			}
		}
		//print_r($this->request->header('username')); die;
		$this->Auth->allow();
	}
	
	public function generateRandomString($length = 15) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
	}
	
	function distance($lat1, $lon1, $lat2, $lon2, $unit="K") {

	  $theta = $lon1 - $lon2;
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	  $dist = acos($dist);
	  $dist = rad2deg($dist);
	  $miles = $dist * 60 * 1.1515;
	  $unit = strtoupper($unit);

	  if ($unit == "K") {
		  return ($miles * 1.609344);
	  } else if ($unit == "N") {
		  return ($miles * 0.8684);
	  } else {
		  return $miles;
	  }
	}
	
	
	public function userAuthenticate($username = null, $user_token = null)
    { 
        $this->loadModel('Users');
        $this->loadModel('UserDevices');
		// Check if user with the provided username exists

        $users = $this->Users->find('all',['contain' => [
				'UserDevices'=> function ($q) use ($user_token) {
					return $q->where(['UserDevices.usertoken'=>$user_token]);
				}
		]])->where(['Users.email'=>$username])->first();
		
        if (empty($users)) {
            return false;
        } else {
            if(count($users['user_devices'])==0) {
				return false;
			} else {
				///// add by sahoo
				$this->session_user_id = $users['id'];
				$this->session_user_first_name = $users['first_name'];
				$this->session_user_last_name = $users['last_name'];
				return true;
			}
        }
    }
}
