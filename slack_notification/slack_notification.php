<?php
/**
 * Quicksilver Script: Slack Notification
 * Description: Send a notification to a Slack channel when code is deployed to Pantheon.
 */

$pantheon_yellow = '#FFDC28';
$slack_channel = '#firehose'; // The Slack channel to post to.

/**
 * class Slack_Text - a basic { type, text } object to embed in a block
 */
class Slack_Text {
    public $type; // 'mrkdwn' or 'plain_text'
    public $text;
    public function __construct(string $text = '', string $type = 'mrkdwn') {
        $this->type = $type;
        $this->text = $text;
    }
}
/**
 * class Slack_Simple_Block - a single column block of content
 */
class Slack_Simple_Block {
    public $type; // 'section' or 'header'
    public $text; // Slack_Text
    public function __construct(Slack_Text $text, string $type = 'section') {
        $this->type = $type;
        $this->text = $text;
    }
}
/**
 * class Slack_Multi_Block - a multi-column block of content (very likely 2 cols)
 */
class Slack_Multi_Block {
    public $type = 'section';
    public $fields; // array of Slack_Text
    public function __construct(array $fields) {
        $this->fields = $fields;
    }
}
/**
 * class Slack_Divider_Block - a divider block
 */
class Slack_Divider_Block {
    public $type = 'divider';
}
/**
 * class Slack_Context_Block - a context block
 */
class Slack_Context_Block {
    public $type = 'context';
    public $elements; // array of Slack_Text
    public function __construct(array $elements) {
        $this->elements = $elements;
    }
}

// some festive icons for the header based on the workflow we're running
$icons = [
    'deploy' => ':rocket:',
    'sync_code' => ':computer:',
    'sync_code_external_vcs' => ':computer:',
    'clear_cache' => ':broom:',
    'clone_database' => ':man-with-bunny-ears-partying:',
    'deploy_product' => ':magic_wand:',
    'create_cloud_development_environment' => ':lightning_cloud:',
];

// Extract workflow information
$workflow_type = $_POST['wf_type'];
$workflow_name = ucfirst(str_replace('_', ' ', $workflow_type));
// Uncomment the following line to see the workflow type.
// printf("Workflow type: %s\n", $workflow_type);
$site_name = $_ENV['PANTHEON_SITE_NAME'];
$environment = $_ENV['PANTHEON_ENVIRONMENT'];

// Create base blocks for all workflows
$blocks = [
    new Slack_Simple_Block(new Slack_Text("{$icons[$workflow_type]} {$workflow_name}", 'plain_text'), 'header'),
    new Slack_Multi_Block([
        new Slack_Text("*Site:* <https://dashboard.pantheon.io/sites/" . PANTHEON_SITE . "#{$environment}/code|{$site_name}>"),
        new Slack_Text("*Environment:* <http://{$environment}-{$site_name}.pantheonsite.io|{$environment}>"),
        new Slack_Text("*Initiated by:* {$_POST['user_email']}"),
    ]),
];

// Add custom blocks based on the workflow type. Note that slack_notification.php must appear in your pantheon.yml for each workflow type you wish to send notifications on.
switch ($workflow_type) {
    case 'deploy':
        $deploy_message = $_POST['deploy_message'];
        $blocks[] = new Slack_Simple_Block(new Slack_Text("*Deploy Note:*\n{$deploy_message}"));
        break;

    case 'sync_code':
    case 'sync_code_external_vcs':
        // Get the time, committer, and message for the most recent commit
        $committer = trim(`git log -1 --pretty=%cn`);
        $hash = trim(`git log -1 --pretty=%h`);
        $message = trim(`git log -1 --pretty=%B`);
        $blocks[] = new Slack_Multi_Block([
            new Slack_Text("*Commit:* {$hash}"),
            new Slack_Text("*Committed by:* {$committer}"),
        ]);
        $blocks[] = new Slack_Simple_Block(new Slack_Text("*Commit Message:*\n{$message}"));
        break;

    case 'clear_cache':
        $blocks[] = new Slack_Simple_Block(new Slack_Text("*Action:*\nCaches cleared on <http://{$environment}-{$site_name}.pantheonsite.io|{$environment}>."));
        break;

    case 'clone_database':
        $blocks[] = new Slack_Multi_Block([
            new Slack_Text("*Cloned from:* {$_POST['from_environment']}"),
            new Slack_Text("*Cloned to:* {$environment}"),
        ]);
        break;

    default:
        $description = $_POST['qs_description'] ?? 'No additional details provided.';
        $blocks[] = new Slack_Simple_Block(new Slack_Text("*Description:*\n{$description}"));
        break;
}

// Add a divider block at the end of the message
$blocks[] = new Slack_Divider_Block();

// Prepare attachments with yellow sidebar
$attachments = [
    [
        'color' => $pantheon_yellow,
        'blocks' => $blocks,
    ],
];

// echo "Blocks:\n";
// print_r( $blocks ); // DEBUG

// Send the Slack notification
_post_to_slack($attachments);

/**
 * Send a notification to Slack
 *
 * @param array $attachments The array of attachments to include in the Slack message.
 */
function _post_to_slack($attachments) {
    // Uncomment the following line to debug the attachments array.
    // echo "Attachments - Raw:\n"; print_r( $attachments ); echo "\n";

    $slack_token = pantheon_get_secret('slack_deploybot_token'); // Set the token name to match the secret you added to Pantheon.

    $post = [
        'channel' => '#firehose', // The Slack channel to post to.
        'blocks' => $blocks,
    ];

    $payload = json_encode($post);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/chat.postMessage');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $slack_token,
        'Content-Type: application/json; charset=utf-8',
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    print("\n==== Posting to Slack ====\n");
    $result = curl_exec($ch);
    $response = json_decode($result, true);

    if (!$response['ok']) {
        print("Error: " . $response['error'] . "\n");
        error_log("Slack API error: " . $response['error']);
    } else {
        print("Message sent successfully!\n");
    }

    curl_close($ch);
}
