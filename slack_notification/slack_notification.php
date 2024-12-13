<?php
/**
 * Quicksilver Script: Slack Notification
 * Description: Send a notification to a Slack channel when code is deployed to Pantheon.
 */

/**
 * Configuration options.
 * 
 * Make changes here to customize the behavior of your notification.
 * 
 * $slack_channel   The Slack channel to post to.
 * $type            Determines the Slack API to use for the message. Expects 'attachments' or 'blocks'. Blocks is more modern (our attachment has blocks embedded in it), but Attachments allows the distinct sidebar color (below).
 * $secret_key      The key for the Pantheon Secret containing the bot token.
 * $pantheon_yellow A color for the sidebar that appears to the left of the Slack message (if 'attachments' is the type used).
 */
$slack_channel = '#firehose';
$type = 'attachments';
$secret_key = 'slack_deploybot_token';
$pantheon_yellow = '#FFDC28';

/**
 * A basic { type, text } object to embed in a block
 * 
 * @param string $text The text message to be sent to Slack.
 * @param string $type The type of notification to send.
 * @param string $block_type The type of block to be sent in the Slack message.
 * 
 * @return array
 */
function _create_text_block( string $text = '', string $type = 'mrkdwn', string $block_type = 'section' ) {
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

/**
 * Some festive emoji for the Slack message based on the workflow we're running.
 * 
 * @return array
 */
function _get_emoji() {
	// Edit these if you want to change or add to the emoji used in Slack messages.
	return [
		'deploy' => ':rocket:',
		'sync_code' => ':computer:',
		'sync_code_external_vcs' => ':computer:',
		'clear_cache' => ':broom:',
		'clone_database' => ':man-with-bunny-ears-partying:',
		'deploy_product' => ':magic_wand:',
		'create_cloud_development_environment' => ':lightning_cloud:',
	];
}

/**
 * Get the type of the current workflow from the $_POST superglobal.
 * 
 * @return string
 */
function _get_workflow_type() {
	return $_POST['wf_type'];
}

/**
 * Extract a human-readable workflow name from the workflow type.
 * 
 * @return string
 */
function _get_workflow_name() {
	return ucfirst(str_replace('_', ' ', _get_workflow_type()));
}

// Uncomment the following line to see the workflow type.
// printf("Workflow type: %s\n", _get_workflow_type());

/**
 * Get Pantheon environment variables from the $_ENV superglobal.
 * 
 * @return object
 */
function _get_pantheon_environment() {
	$pantheon_env = new stdClass;
	$pantheon_env->site_name = $_ENV['PANTHEON_SITE_NAME'];
	$pantheon_env->environment = $_ENV['PANTHEON_ENVIRONMENT'];

	return $pantheon_env;
}

/**
 * Create base blocks for all workflows.
 * 
 * @return array
 */
function _create_base_blocks() {
	$icons = _get_emoji();
	$workflow_type = _get_workflow_type();
	$workflow_name = _get_workflow_name();
	$environment = _get_pantheon_environment()->environment;
	$site_name = _get_pantheon_environment()->site_name;

	$blocks = [
		_create_text_block( "{$icons[$workflow_type]} {$workflow_name}", 'plain_text', 'header' ),
		_create_multi_block([
			"*Site:* <https://dashboard.pantheon.io/sites/" . PANTHEON_SITE . "#{$environment}/code|{$site_name}>",
			"*Environment:* <http://{$environment}-{$site_name}.pantheonsite.io|{$environment}>",
			"*Initiated by:* {$_POST['user_email']}",
		]),
	];

	return $blocks;
}

/**
 * Add custom blocks based on the workflow type. 
 * 
 * Note that slack_notification.php must appear in your pantheon.yml for each workflow type you wish to send notifications on.
 *
 * @return array
 */
function _get_blocks_for_workflow() {
	$workflow_type = _get_workflow_type();
	$blocks = _create_base_blocks();
	$environment = _get_pantheon_environment()->environment;
	$site_name = _get_pantheon_environment()->site_name;

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

	return $blocks;
}

// Uncomment to debug the blocks.
// echo "Blocks:\n";
// print_r( _get_blocks_for_workflow() );

/**
 * Prepare Slack POST content as an attachment with yellow sidebar.
 *
 * @return array
 */
function _get_attachments() {
	global $pantheon_yellow;
	return [
		[
			'color' => $pantheon_yellow,
			'blocks' => _get_blocks_for_workflow(),
		]
	];
}

// Uncomment the following line to debug the attachments array.
// echo "Attachments:\n"; print_r( $attachments ); echo "\n";

/**
 * Send a notification to Slack
 */
function _post_to_slack() {
	global $slack_channel, $type, $secret_key;

	$attachments = _get_attachments();
	$blocks = _get_blocks_for_workflow();
	$slack_token = pantheon_get_secret($secret_key); 

	$post['channel'] = $slack_channel;

	// Check the type and adjust the payload accordingly.
	if ( $type === 'attachments' ) {
		$post['attachments'] = $attachments;
	} elseif ( $type === 'blocks' ) {
		$post['blocks'] = $blocks;
	} else {
		throw new InvalidArgumentException("Unsupported type: $type");
	}

	// Uncomment to debug the payload.
	// echo "Payload: " . json_encode($post, JSON_PRETTY_PRINT) . "\n";
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

// Send the Slack notification
_post_to_slack();