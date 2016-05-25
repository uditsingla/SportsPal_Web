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
 ?>
