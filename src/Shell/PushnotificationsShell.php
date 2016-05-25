<?php

namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\PushnotificationComponent; 

class PushnotificationsShell extends Shell {

	public function initialize()
    {
        parent::initialize();
        $this->loadModel('Notifications');
        $this->loadModel('Users');
		$this->Pushnotification = new PushnotificationComponent(new ComponentRegistry());
    }

    /**
     * retrieveNotifications method
     *
     * @return void
     * @access public   
     */
    public function retrieveNotifications() {
        
        // Fetch the Notifications from the database
        $notifications_data = $this->Notifications->getNotifications();

        // Check if notifications were retrieved
			if(count($notifications_data)>0) {
                $processed_notifications = array();
                $user_id = '';
                $user_email = '';
                $user_notification_setting = array();
                $user_devices = array();
                // Process each notification entry
                foreach ($notifications_data as $notification) {
                    // Pick up the settings for the user is not same as the previous notification user
                   
                    if($notification['user_id']!='') {
                        // Reset user's devices and notification settings
                        $user_notification_setting = array();
                        $user_devices = array();
                        
                        $user_id = $notification['user_id'];
                        // Fetch the notifications settings and devices for the user id
                        $user_details = $this->Users->fetchUserDetails($user_id);
						
                        if($user_details AND count($user_details)==1) {
                         if(isset($user_details->user_devices) && !empty($user_details->user_devices)) {
                              $user_devices = $user_details->user_devices;
                          }
						}
                    }
                    
                    $send_notification=1;
                    $current_time=date("Y-m-d H:i A");
                    
                    
                    
                    if($send_notification)
                    {
                     
                            // Check if user has allowed push notifications
                           
                                if(!empty($user_devices)) {
                                      
                                    $notification_sent_status =TRUE;
                                    // Send Push notification to all devices of user
                                   foreach($user_devices as $device) {
                                       // Send push notification
                                       $push_notification = $this->Pushnotification->sendMessage($device['device_type'], $device['device_token'], $notification['message'], $notification['payload']);
                                    
                                    }
                                }
                                 $processed_notifications[] = $notification['id'];
                           
                        }
                        
                }
                    
            }
   
                // Check if there were some processed entries
                if(count($processed_notifications) > 0) {
                    // Delete the processed entries from dataase
                    //$delete_records = $this->Notifications->deleteNotifications($processed_notifications);
                }
        die;
    }
}

?>