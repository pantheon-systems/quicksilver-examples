<?php
/**
 * Quicksilver Script: Slack Notification
 * Description: Send a notification to a Slack channel when code is deployed to Pantheon.
 */

$pantheon_yellow = '#FFDC28';
$slack_channel = '#firehose'; // The Slack channel to post to.

/**
 * A basic { type, text } object to embed in a block
 * 
 * @param string $text The text message to be sent to Slack.
 * @param string $type The type of notification to send.
 * @param string $block_type The type of block to be sent in the Slack message.
 * 
 * @return array
 */
function _create_text_block( string $text = '', string $type = 'mrkdown', string $block_type = 'section' ) {
	return [
		'type' => $block_type,
		'text' => [
			'type' => $type,
			'text' => $text,
		],
	];
}

/**
 * A multi-column block of content (very likely 2 cols)
 * 
 * @param array $fields The fields to send to the multi-column block.
 * @return array 
 */
function _create_multi_block( array $fields ) {
	return [
		'type' => 'section',
		'fields' => array_map( function( $field ) {
			return [
				'type' => 'mrkdwn',
				'text' => $field,
			];
		}, $fields )
	];
}


/**
 * Creates a context block for a Slack message.
 *
 * @param array $elements An array of text elements to be included in the context block.
 * @return array The context block formatted for a Slack message.
 */
function _create_context_block( array $elements ) {
	return [
		'type' => 'context',
		'elements' => array_map( function( $element ) {
			return [
				'type' => 'mrkdwn',
				'text' => $element,
			];
		}, $elements ),
	];
}

/**
 * A divider block
 * 
 * @return array 
 */
function _create_divider_block() {
  return ['type' => 'divider'];
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
	_create_text_block( "{$icons[$workflow_type]} {$workflow_name}", 'plain_text', 'header' ),
	_create_multi_block([
		"*Site:* <https://dashboard.pantheon.io/sites/" . PANTHEON_SITE . "#{$environment}/code|{$site_name}>",
		"*Environment:* <http://{$environment}-{$site_name}.pantheonsite.io|{$environment}>",
		"*Initiated by:* {$_POST['user_email']}",
	]),
];

// Add custom blocks based on the workflow type. Note that slack_notification.php must appear in your pantheon.yml for each workflow type you wish to send notifications on.
switch ($workflow_type) {
	case 'deploy':
		$deploy_message = $_POST['deploy_message'];
		$blocks[] = _create_text_block("*Deploy Note:*\n{$deploy_message}");
		break;

	case 'sync_code':
	case 'sync_code_external_vcs':
		// Get the time, committer, and message for the most recent commit
		$committer = trim(`git log -1 --pretty=%cn`);
		$hash = trim(`git log -1 --pretty=%h`);
		$message = trim(`git log -1 --pretty=%B`);
		$blocks[] = _create_multi_block([
			"*Commit:* {$hash}",
			"*Committed by:* {$committer}",
		]);
		$blocks[] = _create_text_block("*Commit Message:*\n{$message}");
		break;

	case 'clear_cache':
		$blocks[] = _create_text_block("*Action:*\nCaches cleared on <http://{$environment}-{$site_name}.pantheonsite.io|{$environment}>.");
		break;

	case 'clone_database':
		$blocks[] = _create_multi_block([
			"*Cloned from:* {$_POST['from_environment']}",
			"*Cloned to:* {$environment}",
		]);
		break;

	default:
		$description = $_POST['qs_description'] ?? 'No additional details provided.';
		$blocks[] = _create_text_block("*Description:*\n{$description}");
		break;
}

// Add a divider block at the end of the message
$blocks[] = _create_divider_block();

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
 * @param string $slack_channel The channel to send the message to (defined at the top of the script).
 */
function _post_to_slack($attachments, $slack_channel) {
	// Uncomment the following line to debug the attachments array.
	// echo "Attachments - Raw:\n"; print_r( $attachments ); echo "\n";

	$slack_token = pantheon_get_secret('slack_deploybot_token'); // Set the token name to match the secret you added to Pantheon.

	$post = [
		'channel' => $slack_channel,
		'attachments' => $attachments,
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
