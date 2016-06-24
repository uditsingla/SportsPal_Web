<?php




// Setting the device type constants
define('DEVICE_TYPE_IOS', 'IOS');
define('DEVICE_TYPE_ANDROID', 'ANDROID');

/***** APNS GATEWAY SETTINGS *****/
define('IS_SANDBOX', TRUE);
if(IS_SANDBOX == TRUE) { 
    define('APPLE_GATEWAY_URL', 'ssl://gateway.sandbox.push.apple.com:2195');
    define('APPLE_CERTIFICATE_FILE_PATH', 'webroot/ios_certificate/pushcert.pem');
}
else   { 
    define('APPLE_GATEWAY_URL', 'ssl://gateway.push.apple.com:2195');
    define('APPLE_CERTIFICATE_FILE_PATH', 'webroot/ios_certificate/pushcert.pem');
}
define('APPLE_PASS_PHRASE', 'sportspal');

/***** GCM GATEWAY SETTINGS *****/


define('GCM_API_KEY', 'AIzaSyBXCTk5VPL1NFwQIxvj2kPJx54L_jb9OdE');


/************ PUBNUB SETTINGS ****************************/
define('PUBLISH_KEY', 'pub-c-1379dcea-5b34-47a9-abd3-647d93a520d4');
define('SUBSCRIBE_KEY', 'sub-c-ec7d5b86-35f5-11e6-82e8-02ee2ddab7fe');
define('SECRET_KEY', 'sec-c-YmYyYWNkM2ItMzdlYi00Zjg5LWFhZTYtOWEwZDdhMWY2NmNj');
define('AUTH_KEY', 'auth-c-1379dcea-5b34-47a9-ec7d5b86-82e8');
define('CIPHER_KEY', false);
define('SSL_ON', false);



 ?>
