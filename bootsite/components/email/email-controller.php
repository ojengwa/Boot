<?php

include_once(dirname(dirname(dirname(__FILE__))) . "/libraries/aws/aws-autoloader.php");
include_once(dirname(dirname(dirname(__FILE__))) . "/models/Database.class.php");
include_once(dirname(dirname(dirname(__FILE__))) . "/libraries/Mustache/Autoloader.php");

use Aws\Ses\SesClient;

Database::connect();

$email_buffer_table_name = 'email_buffer';

/**
 * Converts string of format Mr James John <jamesjohn@gmail.com>
 * Into a send appropriate array
 *
 */
function emailToArray($emailaddy) {
	$angle_pos = strpos($emailaddy, "<");
	if ($angle_pos > 0) {
		$emailaddy = array(trim(substr($emailaddy, 0, $angle_pos)) => trim(substr($emailaddy, $angle_pos + 1, strlen($emailaddy) - ($angle_pos+2)) ));
	}
	else
		$emailaddy = array("" => trim($emailaddy));
	
	return $emailaddy;
}         
/**
 * Send email
 *
 */
function bsSendEmail($from, $to, $subject, $text_message, $html_message = "", $cc=array(), $bcc=array(), $additional_headers="") 
{
	// die($to);
	addEmailToDB($from, $to, $subject, $text_message, $html_message, $cc, $bcc, $additional_headers);
	processEmail();
}


/**
 * Used to send email.
 *
 * It currently uses Amazon SES to send all email. The appropriate
 * Amazon SES keys have to be set in your bootsite.php file. Note that
 * the to, cc and bcc paramters are arrays and not text
 *
 */

function addEmailToDB($from, $to, $subject, $text_message, $html_message = "", $cc=array(), $bcc=array(), $additional_headers="") 
{
		// die($to);
		
		global $email_buffer_table_name;
		
		$CC = "";
		foreach ($cc as $key => $value) {
			$CC.= $key;
			$CC .= ",";
		}
		$BCC = "";
		foreach ($bcc as $key => $value) {
			$BCC.= $key;
			$BCC .= ",";
		}
		
		//echo("INSERT INTO {$email_buffer_table_name} (to, from, subject, text_message, html_message, cc, bcc, additional_headers) 
	    //	VALUES(:toemail, :fromemail, :subject, :textmsg, :htmlmsg, :cc, :bcc, :additional_headers)");
			
	    $query = Database::$pdo->prepare("INSERT INTO {$email_buffer_table_name} (email_to, email_from, email_subject, text_message, html_message, cc, bcc, additional_headers) 
	    	VALUES(:toemail, :fromemail, :subject, :textmsg, :htmlmsg, :cc, :bcc, :additional_headers)");
        $query->bindValue(':toemail', serialize($to) , PDO::PARAM_STR);
       	$query->bindValue(':fromemail', serialize($from),  PDO::PARAM_STR);
       	$query->bindValue(':subject', $subject,  PDO::PARAM_STR);
       	$query->bindValue(':textmsg', $text_message, PDO::PARAM_STR);
       	$query->bindValue(':htmlmsg', $html_message, PDO::PARAM_STR);
       	$query->bindValue(':cc', $CC,  PDO::PARAM_STR);
       	$query->bindValue(':bcc', $BCC , PDO::PARAM_STR);
       	$query->bindValue(':additional_headers', $additional_headers, PDO::PARAM_STR);
        
        $query->execute();

		// print_r(serialize($to));
	//	echo "\nPDOStatement::errorInfo():\n";
	//	$arr = $query->errorInfo();
	//	print_r($arr);
	//	die();
		
		processEmail();
}



function processEmail(){
		global $email_buffer_table_name;
		
		echo "Starting processing emails";
		
	   $query = Database::$pdo->prepare("SELECT * FROM {$email_buffer_table_name}");
       $query->execute();
       $results = $query->fetchAll();
	
	    foreach ($results as $row) {
	    	echo "Sending email";
			// print_r($row);
			

	    	$from = array(unserialize($row['email_from']));
	    	$to = array(unserialize($row['email_to']));
	    	$subject = $row['email_subject'];
	    	$text_message = $row['text_message'];
	    	$html_message = $row['html_message'];
	    	$cc = array();
	    	$bcc = array();
	    	$additional_headers = $row['additional_headers'];
	    	// $CC = explode(",", $cc);
	    	// $BCC = explode(",", $bcc);
			// echo "To: " . $to;
		
	    	sendEmailNow($from, $to, $subject, $text_message, $html_message, $cc, $bcc, $additional_headers);
			
			print_r($from);
			print_r($to);
			die();
	    }
}

function sendEmailNow($from, $to, $subject, $text_message, $html_message = "", $cc=array(), $bcc=array(), $additional_headers="") {

	$AWS_KEY = BOOTSITE_AWS_KEY;
	$AWS_SECRET_KEY = BOOTSITE_AWS_SECRET_KEY;

	$amazonSes = SesClient::factory(array(
	    'key'    => $AWS_KEY,
	    'secret' => $AWS_SECRET_KEY,
	    'region' => 'us-east-1'
	));

	$content['Source'] = $from[0];
	$content['Destination']['ToAddresses'] = $to;
	$content['Destination']['BccAddresses'] = $bcc;
	$content['Destination']['CcAddresses'] = $cc;

	$content['Message']['Subject']['Data'] = $subject;
	$content['Message']['Subject']['Charset'] = "UTF-8";

	if ($html_message === "")
		$html_message = $text_message;

	$content['Message']['Body']['Text']['Data'] = $text_message;
	$content['Message']['Body']['Text']['Charset'] = "UTF-8";
	$content['Message']['Body']['Html']['Data'] = $html_message;
	$content['Message']['Body']['Html']['Charset'] = "UTF-8";

	try {
		$response = $amazonSes->sendEmail($content);
	} catch (Exception $e) {
		echo((string)$e->getMessage());
		return (string)$e->getMessage();
	}

	return 'sent';
}

/**
 * Used to send email from a HTML template
 *
 * The templates are loaded, all variables in the templates parsed and replaced
 *
 */

function sendtemplatemail($from, $to, $subject, $templatename, $templatevars, $cc=array(), $bcc=array(), $additional_headers=""){
	\Mustache_Autoloader::register();

	$options =  array('extension' => '.html');
	$m = new \Mustache_Engine(array(
    							'loader' => new \Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/view/email', $options),
								));

	echo $m->render($templatename, $templatevars);
	sendmailnow($from, $to, $subject, $text_message, $html_message = "", $cc=array(), $bcc=array(), $additional_headers="");

}
