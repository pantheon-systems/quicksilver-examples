<?php
// Find out what tag we are on and get the annotation.
$deploy_tag = `git describe --tags`;
$annotation = `git tag -l -n99 $deploy_tag`;
$deploy_message = preg_replace('/pantheon_(\w+)_(\d+)/', '', $annotation);
$oAuthParams = array();
$signature = '';

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/secrets.json'), 1);
if ($secrets == FALSE) {
  die('No secrets file found. Aborting!');
}

$url='https://api.twitter.com/1.1/statuses/update.json';
$httpMethod='post';
$oAuthConsumerSecret=$secrets['twitter_oauth_consumer_secret'];
$oAuthTokenSecret=$secrets['twitter_oauth_token_secret'];

$oAuthParams['status'] = $_POST['user_email'] . ' deployed to the '. PANTHEON_ENVIRONMENT . ' environment of '. $_ENV['PANTHEON_SITE_NAME'];;
$oAuthParams['oauth_consumer_key']	=$secrets['twitter_oauth_consumer_key'];
$oAuthParams['oauth_nonce']= makeNonce();
$oAuthParams['oauth_signature_method']='HMAC-SHA1';
$oAuthParams['oauth_timestamp']=time();
$oAuthParams['oauth_token']=$secrets['twitter_oauth_token'];
$oAuthParams['oauth_version']='1.0';

function makeNonce() {
  //Seed the nonce with the time
  $nonce=time();
  //pad the nonce so that base64 encoding will be pretty
  for ($i=0;$i<14;$i++){
    $nonce.= chr(mt_rand(65,90));
  }
  //shuffle the string and base64 encode it
  $nonce=str_shuffle($nonce);
  return base64_encode($nonce);
}
/* Create the OAuth signature based on the provided parameters */
function oAuthSign($params,$consumerSecret,$tokenSecret,$url,$httpMethod){

  $paramString='';
  $signatureBase='';
  $signingKey='';

//Sort the parameter array alphabetically by key
  ksort($params);
  //Concatenate the parameter array and raw url encode the values
  $numParams = count($params);
  $counter=0;
  foreach ($params as $key => $value){
    $counter+=1;
    $paramString .= $key.'='.rawurlencode($value);
    if ($counter < $numParams){
      $paramString .= '&';
    }
  }
  //Ensure that the HTTP method is upper case
  $httpMethod=strtoupper($httpMethod);
  //Construct the signature base
  $signatureBase .= $httpMethod . '&';
  $signatureBase .= rawurlencode($url). '&';
  $signatureBase .= rawurlencode($paramString);
  //construct the signing key
  $signingKey = $consumerSecret. '&' . $tokenSecret;
  //construct the signature
  $signature = rawurlencode(base64_encode(hash_hmac('SHA1',$signatureBase, $signingKey, true)));
  return $signature;
}

$signature= oAuthSign($oAuthParams,$oAuthConsumerSecret,$oAuthTokenSecret,$url,$httpMethod);
$payload = 'status='.rawurlencode($oAuthParams['status']);
//Construct the oAuth Authentication header
$headerString='Authorization: OAuth oauth_consumer_key="'.$oAuthParams['oauth_consumer_key'].'", oauth_nonce="'.$oAuthParams['oauth_nonce'].'", oauth_signature="'.$signature.'", oauth_signature_method="'.$oAuthParams['oauth_signature_method'].'", oauth_timestamp="'.$oAuthParams['oauth_timestamp'].'", oauth_token="'.$oAuthParams['oauth_token'].'", oauth_version="'.$oAuthParams['oauth_version'].'"';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPHEADER, array($headerString));
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
print("\n==== Posting to Twitter ====\n");
$result = curl_exec($ch);
print("RESULT: $result");
print("\n===== Post Complete! =====\n");
curl_close($ch);
