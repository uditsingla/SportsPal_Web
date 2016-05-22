<?php
App::uses('Component', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class EmailsComponent extends Component {
    
    /**
	 * sendEmail
     * Compile and send email
     *
     * @param string $to Email receiver
     * @param string $subject Email subject
     * @param string $message Email body
     * @param string $template Template to be used
     * @param string $senderName From name to be used in the email
     * @param string $senderEmail From email
     * @param string $googleNowMarkup Google Now Markup to be sent in the email
     * @param string $clientRegisterLink Link to registration page
     * @param string $contactUsLink Link to contact us page
     * @param string $imageUri Uri for preparing image url
     * @param array $Cc List of emails to put in Cc
     * @param array $Bcc List of emails to put in Bcc
     * @return boolean
     * @access public
     */
    public function sendEmail ($to, $subject, $message, $template, $senderName, $senderEmail, $googleNowMarkup, $clientRegisterLink, $contactUsLink, $imageUri, $Cc = array(), $Bcc = array()) {
        // Check if sender email was sent
        //if(empty($senderEmail)) {
            // Assign email address from settings otherwise
            $replyToEmail = $senderEmail;
            $senderEmail = SENDER_EMAIL; // TODO :: Temporarily sending all emails through the email id in the settings file
        //}
        
        // Setup the email to be sent
        $Email = new CakeEmail();
        $Email->config('default');
        $Email->from(array($senderEmail => $senderName));
        $Email->replyTo(array($replyToEmail => $senderName));
        $Email->to($to);
        $Email->subject($subject);
        if(count($Cc)>0) {
            $Email->cc($Cc);
        }
        if(count($Bcc)>0) {
            $Email->bcc($Bcc);
        }
        // Configure the message body
        $Email->template($template, 'default'); // view and layout files
        $Email->emailFormat('html');
        $Email->viewVars(array('message' => $message, 'client_register_link' => $clientRegisterLink, 'contact_us_link' => $contactUsLink, 'image_uri' => $imageUri, 'google_now_markup' => $googleNowMarkup));
         
        // Send compiled email
        try { 
            if(!$Email->send()) { 
                $this->log("Error sending email");
                return array('status' => false, 'message' => 'Error sending email"');
            }
            else {
                return array('status' => true, 'message' => 'Email sent successfully');
            }
        } 
        catch(Exception $e) { 
            $this->log("Failed to send email: ".$e->getMessage());
            return array('status' => false, 'message' => "Failed to send email: ".$e->getMessage());
        } 
    }
}
?>