<?php

namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Controller\Component;

class PushnotificationComponent extends Component {
    
    public $components = ['Apns','C2DM'];
    
    /**
     * sendMessage method
     * 
     * @param string $type
     * @param string $deviceToken
     * @param string $message
     * @param array $payLoadData
     * @return boolean $response
     */
    public function sendMessage($type, $deviceToken, $message, $payLoadData = array()) {
        
        $result = FALSE;
        switch ($type) {
            case 'ios':
        
                // configure settings..
                $params = array(
                    'gateway' => APPLE_GATEWAY_URL,
                    'cert' => APPLE_CERTIFICATE_FILE_PATH,
                    'passphrase' => APPLE_PASS_PHRASE,
                    'message' => $message
                );
                
                // send push notification via apns
                $result = $this->Apns->sendPushMessage($deviceToken, $params, $payLoadData);
                
                break;
            case 'Android':
                
                $params['registrationIds'] = array($deviceToken);
                // send push notification via gcm
                $result = $this->C2DM->sendMessage('AIzaSyBXCTk5VPL1NFwQIxvj2kPJx54L_jb9OdE', $params['registrationIds'], $message, $payLoadData);
                
                break;
            default:
                $result = FALSE;
                break;            
        }
        $response = ($result == TRUE) ? 'Message successfully delivered.' : 'Message not delivered.';
            
        return array('status' => $result, 'message' => $response);
    }
    
}