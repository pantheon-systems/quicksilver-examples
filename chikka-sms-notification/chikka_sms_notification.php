<?php
// Default values for parameters
$defaults = array(
  'chikka_url' => 'https://post.chikka.com/smsapi/request',
	'mobile_number' => '639471729649',
);
// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = _get_secrets(array('chikka_client_id', 'chikka_client_secret', 'chikka_accesscode'), $defaults);

$number = $secrets['mobile_number'];

$workflow_description = ucfirst($_POST['stage']) . ' ' . str_replace('_', ' ', $_POST['wf_type']);

// Customize the message based on the workflow type.  Note that hipchat_notification.php
// must appear in your pantheon.yml for each workflow type you wish to send notifications on.
switch($_POST['wf_type']) {
  case 'deploy':
    // Find out what tag we are on and get the annotation.
    $deploy_tag = `git describe --tags`;
    $deploy_message = $_POST['deploy_message'];
    // Prepare the message
    $text =  $_POST['user_fullname'] . ' deployed ' . $_ENV['PANTHEON_SITE_NAME'] . '
    On branch "' . PANTHEON_ENVIRONMENT . '"Workflow: ' . $workflow_description . '
    Deploy Message: ' . htmlentities($deploy_message);
    break;
  case 'sync_code':
    // Get the committer, hash, and message for the most recent commit.
    $committer = `git log -1 --pretty=%cn`;
    $email = `git log -1 --pretty=%ce`;
    $message = `git log -1 --pretty=%B`;
    $hash = `git log -1 --pretty=%h`;
    // Prepare the message
    $text = $_POST['user_fullname'] . ' committed to' . $_ENV['PANTHEON_SITE_NAME'] . '
    On branch "' . PANTHEON_ENVIRONMENT . '"Workflow: ' . $workflow_description . '
    - ' . htmlentities($message);
    break;
  default:
    $text = "Workflow $workflow_description" . $_POST['qs_description'];
    break;
}


  $message = $text;
  if ( sendSMS($number, $message, $secrets['chikka_accesscode'], $secrets['chikka_client_id'], $secrets['chikka_client_secret'], $secrets['chikka_url'] ) == TRUE) {
    echo "Successfully sent SMS to $number";
  } else {
    echo "ERROR";
  }


/**
 * Get secrets from secrets file.
 *
 * @param array $requiredKeys  List of keys in secrets file that must exist.
 */
function _get_secrets($requiredKeys, $defaults)
{
  $secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
  if (!file_exists($secretsFile)) {
    die('No secrets file found. Aborting!');
  }
  $secretsContents = file_get_contents($secretsFile);
  $secrets = json_decode($secretsContents, 1);
  if ($secrets == FALSE) {
    die('Could not parse json in secrets file. Aborting!');
  }
  $secrets += $defaults;
  $missing = array_diff($requiredKeys, array_keys($secrets));
  if (!empty($missing)) {
    die('Missing required keys in json secrets file: ' . implode(',', $missing) . '. Aborting!');
  }
  return $secrets;
}


	// Send / Broadcast SMS
function sendSMS($mobile_number, $message, $chikka_accesscode, $chikka_client_id, $chikka_client_secret, $chikka_url)
	{
		$post = array( 	"message_type" 	=> "SEND",
						"mobile_number" => $mobile_number,
						"shortcode" 	=> $chikka_accesscode,
						"message_id"	=> date('YmdHis'),
						"message"     => urlencode($message),
						"client_id" 	=> $chikka_client_id,
						"secret_key" 	=> $chikka_client_secret);

		$result = curl_request($chikka_url, $post);
		$result = json_decode($result, true);
		if ($result['status'] == '200') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// Reply SMS
  function replySMS($mobile_number, $request_id, $message, $price = 'P2.50', $chikka_accesscode, $chikka_client_id, $chikka_client_secret, $chikka_url)
	{
	  $message_id = date('YmdHis');
		$post = array( 	"message_type" 	=> "REPLY",
						"mobile_number" => $mobile_number,
						"shortcode" 	=> $chikka_accesscode,
						"message_id"	=> $message_id,
						"message" 	=> urlencode($message),
						"request_id" 	=> $request_id,
						"request_cost" 	=> $price,
						"client_id" 	=> $chikka_client_id,
						"secret_key" 	=> $chikka_client_secret);

		$result = curl_request($chikka_url, $post);
		$result = json_decode($result, true);
		if ($result['status'] == '200') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// Reply SMS
	function replySMS2($mobile_number, $request_id, $message, $price = 'P2.50', $chikka_accesscode, $chikka_client_id, $chikka_client_secret, $chikka_url)
	{
	        $message_id = date('YmdHis');
		      $post = array( 	"message_type" 	=> "REPLY",
						"mobile_number" => $mobile_number,
						"shortcode" 	=> $secrets['chikka_accesscode'],
						"message_id"	=> $message_id,
						"message" 	=> urlencode($message),
						"request_id" 	=> $request_id,
						"request_cost" 	=> $price,
						"client_id" 	=> $secrets['chikka_client_id'],
						"secret_key" 	=> $secrets['chikka_client_secret'] );

		$result = curl_request($secrets['chikka_url'], $post);
		$result = json_decode($result, true);
		if ($result['status'] == '200') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// Basic Curl Request
  function curl_request( $URL, $arr_post_body)
	{
		$query_string = "";
		foreach($arr_post_body as $key => $frow) {
			$query_string .= '&'.$key.'='.$frow;
		}

		$curl_handler = curl_init();
		curl_setopt($curl_handler, CURLOPT_URL, $URL);
		curl_setopt($curl_handler, CURLOPT_POST, count($arr_post_body));
		curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $query_string);
		curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($curl_handler);
		if(curl_errno($curl_handler))
		{
			$info = curl_getinfo($curl_handler);
		}
		curl_close($curl_handler);
		return $response;
	}

?>
