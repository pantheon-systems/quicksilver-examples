<?php
/**
 * QuickSilver script to post notifications to Slack
 *
 * Requires:
 * - /files/private/secrets.json with 'slack_url' specifying the url of
 *   the Slack incoming webhook to POST to
 */

// Define some useful classes for creating the $blocks JSON required by Slack
/**
 * class Slack_Message - a basic { type, text } object to embed in a block
 */
class Slack_Message {
  public $type; // 'mrkdwn' or 'plain_text'
  public $text;
  public function __construct( string $text='', string $type='mrkdwn' ) {
    $this->type = $type;
    $this->text = $text;
  }
}
/**
 * class Slack_Simple_Block - a single column block of content
 */
class Slack_Simple_Block {
  public $type; // 'section' or 'header'
  public $text; // Slack_Message
  public function __construct( Slack_Message $text, string $type='section' ) {
    $this->type = $type;
    $this->text = $text;
  }
}
/**
 * class Slack_Multi_Block - a multi-column block of content (very likely 2 cols)
 */
class Slack_Multi_Block {
  public $type; // 'section' or 'header'
  public $fields; // array of Slack_Message
  public function __construct( array $fields ) {
    $this->type   = 'section';
    $this->fields = $fields;
  }
}

// some festive icons for the header based on the workflow we're running
$icons = [
  'deploy'                               => ':rocket: ',
  'sync_code'                            => ':git: ',
  'clear_cache'                          => ':broom: ',
  'clone_database'                       => ':man-with-bunny-ears-partying: ',
  'deploy_product'                       => ':magic-wand: ',
  'create_cloud_development_environment' => ':lightning_cloud: '
];

// define initial blocks common to all workflows
$blocks = [];
$blocks[] = new Slack_Simple_Block(
  new Slack_Message( $icons[ $_POST[ 'wf_type' ] ] . ucfirst( str_replace('_', ' ',  $_POST[ 'wf_type' ] ) ), 'plain_text' ),
  'header'
);
$blocks[] = new Slack_Multi_Block( [
  new Slack_Message( "*Site*\n{$_ENV['PANTHEON_SITE_NAME']}" ),
  new Slack_Message( "*Initated by*\n{$_POST['user_email']}" )
] );
$blocks[] = new Slack_Multi_Block( [
  new Slack_Message( "*Environment*\n{$_ENV['PANTHEON_ENVIRONMENT']}" ),
  new Slack_Message( "*Dashboard*\n<https://dashboard.pantheon.io/sites/" . PANTHEON_SITE . "#" . PANTHEON_ENVIRONMENT . "/code|View Dashboard>" ),
] );


// Add custom blocks based on the workflow type.  Note that slack_notification.php must
// appear in your pantheon.yml for each workflow type you wish to send notifications on.
switch( $_POST[ 'wf_type' ] ) {
  case 'deploy':
    $blocks[] = new Slack_Simple_Block(
      new Slack_Message( "*Deploy message*\n{$_POST[ 'deploy_message' ]}" )
    );
    break;

  case 'sync_code':
    // Get the time, committer, and message for the most recent commit
    $time      = `git log -1 --pretty=%cd --date='format:%c'`;
    $committer = `git log -1 --pretty=%cn`;
    $message   = `git log -1 --pretty=%B`;

    $blocks[] = new Slack_Multi_Block( [
      new Slack_Message( "*Commit time*\n{$time}" ),
      new Slack_Message( "*Committed by*\n{$committer}" )
    ] );
    $blocks[] = new Slack_Simple_Block(
      new Slack_Message( "*Commit message*\n{$message}" )
    );
    break;

  case 'clone_database':
    $blocks[] = new Slack_Multi_Block( [
        new Slack_Message( "*Cloned from*\n{$_POST['from_environment']}" ),
        new Slack_Message( "*Cloned to*\n{$_ENV['PANTHEON_ENVIRONMENT']}" )
    ] );
    break;

  case 'clear_cache':
    // nothing else to say about clearing cache
    break;

  default:
    $blocks[] = new Slack_Simple_Block(
      new Slack_Message( "*Description*\n{$_POST[ 'qs_description' ]}" )
    );
}

// actually post the notification
_post_to_slack( $blocks );


/**
 * Send a notification to slack
 *
 * @param array $blocks - array of objects suitable for jsonifying and posting to Slack
 */
function _post_to_slack( $blocks )
{
  /* Uncomment to debug JSON
    echo "Blocks - Raw:\n"; print_r( $blocks ); echo "\n";
    echo "Blocks - JSON:\n", json_encode( $blocks, JSON_PRETTY_PRINT ), "\n";
  */
  // get secrets from the secrets file - die if we don't find the Slack webhook url
  $secrets = _get_secrets( [ 'slack_url' ] );
  $post = array(
    'username' => 'Pantheon Quicksilver',
    'blocks'   => $blocks
  );
  $payload = json_encode($post);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $secrets[ 'slack_url' ]);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  // watch for messages with `terminus workflow:watch SITENAME` (no .env!)
  print("\n==== Posting to Slack ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("\n===== Post to Slack complete! =====\n");
  curl_close($ch);
}

/**
 * Get secrets from secrets file.
 *
 * @param array $requiredKeys  List of keys that must exist in secrets file
 */
function _get_secrets( $requiredKeys )
{
  $secretsFile = $_SERVER[ 'HOME' ] . '/files/private/secrets.json';
  if ( !file_exists( $secretsFile ) ) {
    die( 'Secrets file ' . $secretsFile . ' not found. Aborting!' );
  }
  $secretsContents = file_get_contents( $secretsFile );
  $secrets = json_decode( $secretsContents, TRUE );
  if ( $secrets == false ) {
    die( 'Could not parse json in ' . $secretsFile . '. Aborting!') ;
  }
  $missing = array_diff( $requiredKeys, array_keys( $secrets ) );
  if ( !empty( $missing ) ) {
    die('Missing required keys in ' . $secretsFile . ': ' . implode(',', $missing) . '. Aborting!');
  }
  return $secrets;
}
