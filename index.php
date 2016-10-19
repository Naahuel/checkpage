<?php
/**
* Run this script periodically with cron
* ---
* Download a website's content and compare it with previous downloaded content
* 
* @requires Linux server (for diff command)
* @version 1.0 
*/

 $url 				  = 'https://www.example.com/content.html';
 $f_previous_content  = 'status.0';
 $f_current_content   = 'status.1';
 $f_diff			  = 'diff_file';
 $email_from          = 'no-reply@example.com';
 $email_to		      = 'to_email@example.com';

 //-------------------------------------------------------------------------------

 // Include PHPMailer Class
 include 'class.phpmailer.php';

 // If the previous status doesn't exist, create a blank file
 if( !file_exists( $f_previous_content ) ){
 	file_put_contents( $f_previous_content, '' );
 }

 // Get current status
 file_put_contents( $f_current_content , url_get_contents( $url ) );

 // Create diff file
 exec( 'diff ' . $f_previous_content . ' ' . $f_current_content . ' > ' . getcwd() . '/' . $f_diff );

 // Delete previous content and set the current one as previous
 unlink( $f_previous_content );
 rename( $f_current_content, $f_previous_content);

 // If the diff file has content, e-mail it
 if( file_get_contents( $f_diff ) ){

 	$email = new PHPMailer();
	$email->From      = $email_from;
	$email->FromName  = 'Check Page';
	$email->Subject   = 'Changes on ' . $url;
	$email->Body      = file_get_contents( $f_diff );
	$email->AddAddress( $email_to );
	$email->AddAttachment( getcwd() . '/' . $f_diff );

	return $email->Send();

 }

 // Function to get the URL's contents using cURL
 function url_get_contents($url, $useragent='cURL', $headers=false, $follow_redirects=true, $debug=false) {

    // initialise the CURL library
    $ch = curl_init();

    // specify the URL to be retrieved
    curl_setopt($ch, CURLOPT_URL,$url);

    // we want to get the contents of the URL and store it in a variable
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

    // specify the useragent: this is a required courtesy to site owners
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

    // ignore SSL errors
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // return headers as requested
    if ($headers==true){
        curl_setopt($ch, CURLOPT_HEADER,1);
    }

    // only return headers
    if ($headers=='headers only') {
        curl_setopt($ch, CURLOPT_NOBODY ,1);
    }

    // follow redirects - note this is disabled by default in most PHP installs from 4.4.4 up
    if ($follow_redirects==true) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
    }

    // if debugging, return an array with CURL's debug info and the URL contents
    if ($debug==true) {
        $result['contents']=curl_exec($ch);
        $result['info']=curl_getinfo($ch);
    }

    // otherwise just return the contents as a variable
    else $result=curl_exec($ch);

    // free resources
    curl_close($ch);

    // send back the data
    return $result;
}