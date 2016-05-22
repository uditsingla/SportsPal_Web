<?php
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class NotificationsComponent extends Component {
 
		
    /**
	 * saveNotification
     *
     * @param array $user_id User Id invited
     * @param array $type type of notification
     * @return boolean
     * @access public
     */
    public function notifyMembers($user_id, $team_id, $sender_id) {
			$payload=array();
			$this->Users = TableRegistry::get('Users');
			$this->Teams = TableRegistry::get('Teams');
			$this->Notifications = TableRegistry::get('Notifications');
			$request_data="";
			$request_data['user_id']=$user_id;
			$request_data['type']=$payload['type']="team_request_by_admin";
			$request_data['message']="You got request to be a part of team";

			$userDetails=$this->Users->getUserdetails($user_id);
			if($userDetails) { 
				$payload['user_details']=$userDetails;
			}
			
			$userDetails=$this->Users->getUserdetails($sender_id);
			if($userDetails) { 
				$payload['sender_details']=$userDetails;
			}
			
			$teamDetails=$this->Teams->getTeamdetails($team_id);
			if($teamDetails) {
				$payload['team_details']=$teamDetails;
			}
			
			$request_data['payload']=json_encode($payload);	
			$user = $this->Notifications->newEntity();
			$Notifications = $this->Notifications->newEntity();
			$Notifications = $this->Notifications->patchEntity($Notifications, $request_data);
			$this->Notifications->save($Notifications);
    } 
    
	
	public function notifyAdmin($user_id, $team_id, $sender_id, $type) {
			$payload=array();
			$this->Users = TableRegistry::get('Users');
			$this->Teams = TableRegistry::get('Teams');
			$this->Notifications = TableRegistry::get('Notifications');
			$request_data="";
			$request_data['user_id']=$user_id;
			if($type=='accepted') {
				$request_data['type']=$payload['type']="request_accepted_by_user";
				$request_data['message']="User accepted team member request";
			} else {
				$request_data['type']=$payload['type']="request_rejected_by_user";
				$request_data['message']="User rejected team member request";
			}
			$userDetails=$this->Users->getUserdetails($user_id);
			if($userDetails) { 
				$payload['user_details']=$userDetails;
			}
			
			$userDetails=$this->Users->getUserdetails($sender_id);
			if($userDetails) { 
				$payload['sender_details']=$userDetails;
			}
			
			$teamDetails=$this->Teams->getTeamdetails($team_id);
			if($teamDetails) {
				$payload['team_details']=$teamDetails;
			}
			
			$request_data['payload']=json_encode($payload);	
			$user = $this->Notifications->newEntity();
			$Notifications = $this->Notifications->newEntity();
			$Notifications = $this->Notifications->patchEntity($Notifications, $request_data);
			$this->Notifications->save($Notifications);
    } 
}
?>