<?php

// An example of using Pantheon's Quicksilver technology to do
// automatic visual regression testing using diffy.website
define("SITE_URL", "https://app.diffy.website");

echo 'Checking if it is test environment deployment.' . PHP_EOL;
if (defined('PANTHEON_ENVIRONMENT') && (PANTHEON_ENVIRONMENT == 'test')) {
    echo 'Stage deployment. Starting visual testing.' . PHP_EOL;
    $diffy = new DiffyVisualregression();
    $diffy->run();
}
else {
    echo 'No it is not Test environment. Skipping visual testing.' . PHP_EOL;
}

class DiffyVisualregression {

  private $jwt;
  private $error;
  private $processMsg = '';
  private $secrets;

  public function run()
  {

      // Load our hidden credentials.
      // See the README.md for instructions on storing secrets.
      $this->secrets = $this->get_secrets(['token', 'project_id']);

      echo 'Starting a visual regression test between the live and test environments...' . PHP_EOL;
      $isLoggedIn = $this->login($this->secrets['token']);
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
      $authorization = 'Authorization: Bearer ' . $this->jwt;
      $curlOptions = array(
        CURLOPT_URL => rtrim(SITE_URL, '/') . '/api/projects/' . $this->secrets['project_id'] . '/compare',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json' , $authorization ),
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POSTFIELDS => json_encode(array(
          'env1' => 'prod',
          'env2' => 'stage',
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
              $this->processMsg .= 'Check out the result here: ' . rtrim(SITE_URL, '/') . '/#/diffs/' . $diffId . PHP_EOL;
              return true;
          }
      } else {
          $this->error = '-1:' . $curlResponse . PHP_EOL;
          return false;
      }
  }

  private function login($token) {
    $curl = curl_init();
    $curlOptions = array(
      CURLOPT_URL => rtrim(SITE_URL, '/') . '/api/auth/key',
      CURLOPT_POST => 1,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
      CURLOPT_POSTFIELDS => json_encode(array(
        'key' => $token,
      ))
    );

    curl_setopt_array($curl, $curlOptions);
    $curlResponse = json_decode(curl_exec($curl));
    $curlErrorMsg = curl_error($curl);
    $curlErrno= curl_errno($curl);
    curl_close($curl);

    if ($curlErrorMsg) {
        $this->error = $curlErrno . ': ' . $curlErrorMsg . PHP_EOL;
        return false;
    }

    if (isset($curlResponse->token)) {
        $this->jwt = $curlResponse->token;
        return true;
    } else {
        $this->jwt = null;
        $this->error = '401: '.$curlResponse->message . PHP_EOL;
        return false;
    }
  }

  private function parseProjectErrors($errors) {
      $errorsString = '';
      foreach ($errors as $key => $error) {
          $errorsString .= $key . ' => ' . $error . PHP_EOL;
      }
      return $errorsString;
  }

  /**
   * Get secrets from secrets file.
   *
   * @param array $requiredKeys List of keys in secrets file that must exist.
   */
  private function get_secrets($requiredKeys)
  {
      $secretsFile = $_SERVER['HOME'].'/files/private/secrets.json';

      if (!file_exists($secretsFile)) {
          die('No secrets file found. Aborting!');
      }
      $secretsContents = file_get_contents($secretsFile);
      $secrets = json_decode($secretsContents, 1);
      if ($secrets == false) {
          die('Could not parse json in secrets file. Aborting!');
      }

      $missing = array_diff($requiredKeys, array_keys($secrets));
      if (!empty($missing)) {
          die('Missing required keys in json secrets file: '.implode(',', $missing).'. Aborting!');
      }

      return $secrets;
  }

}
