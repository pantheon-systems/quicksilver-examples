<?php

// An example of usign Pantheon's Quicksilver technology to do
// automatic visual regression testing using diffy.website
define("SITE_URL", "https://diffy.website");

// Provide the login and password for the project on diffy.website
define('USERNAME', 'add-login-here');
define('PASSWORD', 'add-password-here');

// Provide the Project ID for the project on diffy.website
define('PROJECT_ID', 'add-project-id-here');

if (defined('PANTHEON_ENVIRONMENT') && (PANTHEON_ENVIRONMENT == 'test')) {
    $diffy = new DiffyVisualregression();
    $diffy->run();
}

class DiffyVisualregression {

  private $token;
  private $error;
  private $processMsg = '';

  public function run()
  {
      echo 'Starting a visual regresison test between the live and test environments...' . '\n';
      $isLoggedIn = $this->login(USERNAME, PASSWORD);
      if (!$isLoggedIn) {
          echo $this->error;
          return;
      }

      $compare = $this->compare();
      if (!$compare) {
          echo $this->error;
          return;
      }

      echo $this->processMsg;
      return;
  }

  private function compare()
  {
      $curl = curl_init();
      $authorization = 'Authorization: Bearer ' . $this->token;
      $curlOptions = array(
        CURLOPT_URL => rtrim(SITE_URL, '/') . '/api/projects/' . PROJECT_ID . '/compare',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json' , $authorization ),
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POSTFIELDS => json_encode(array(
          'environments' => 'prod-stage',
          'withRescan' => false
        ))
      );

      curl_setopt_array($curl, $curlOptions);
      $curlResponse = json_decode(curl_exec($curl));
      $curlErrorMsg = curl_error($curl);
      $curlErrno= curl_errno($curl);
      curl_close($curl);

      if ($curlErrorMsg) {
          $this->error = $curlErrno . ': ' . $curlErrorMsg . '\n';
          return false;
      }

      if (isset($curlResponse->errors)) {

          $errorMessages = is_object($curlResponse->errors) ? $this->parseProjectErrors($curlResponse->errors) : $curlResponse->errors;
          $this->error = '-1:' . $errorMessages;
          return false;
      }

      if (strstr($curlResponse, 'diff: ')) {
          $diffId = (int) str_replace('diff: ', '', $curlResponse);
          if ($diffId) {
              $this->processMsg .= 'Check out the result here: ' . rtrim(SITE_URL, '/') . '/ui#/diffs/' . $diffId . '\n';
              return true;
          }
      } else {
          $this->error = '-1:' . $curlResponse . '\n';
          return false;
      }
  }

  private function login($username, $password) {
    $curl = curl_init();
    $curlOptions = array(
      CURLOPT_URL => rtrim(SITE_URL, '/') . '/api/login_check',
      CURLOPT_POST => 1,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_POSTFIELDS => array(
        '_username' => $username,
        '_password' => $password
      )
    );

    curl_setopt_array($curl, $curlOptions);
    $curlResponse = json_decode(curl_exec($curl));
    $curlErrorMsg = curl_error($curl);
    $curlErrno= curl_errno($curl);
    curl_close($curl);

    if ($curlErrorMsg) {
        $this->error = $curlErrno . ': ' . $curlErrorMsg . '\n';
        return false;
    }

    if (isset($curlResponse->token)) {
        $this->token = $curlResponse->token;
        return true;
    } else {
        $this->token = null;
        $this->error = '401: '.$curlResponse->message."\n";
        return false;
    }
  }

  private function parseProjectErrors($errors) {
      $errorsString = '';
      foreach ($errors as $key => $error) {
          $errorsString .= $key . ' => ' . $error . '\n';
      }
      return $errorsString;
  }

}
