<?php

/**
 * @file
 * Sends Autopilot VRT Quicksilver hook data to Slack.
 */
// Retrieve Slack webhook config data
$config_file = $_SERVER['HOME'] . '/files/private/autopilot.json';
$config = json_decode(file_get_contents($config_file), 1);
if ($config == false) {
  die('files/private/autopilot.json found. Aborting!');
}
// The Webhook URL for Slack.
$webook_url = $config['webhook_url'];

// Sample data used if no actual update info in $_POST, e.g. invoking script directly while testing/developing this script.
$updates_info_raw = $_POST['updates_info'] ?? "{\"upstream_ids\": null, \"extension_list\": [{\"update_version\": \"8.x-2.8\", \"version\": \"8.x-2.7\", \"type\": \"module\", \"name\": \"layout_builder_restrictions\", \"title\": \"Layout Builder Restrictions (layout_builder_restrictions)\"}], \"ids\": [25684], \"extensions_commit_message\": \"Updated Modules (layout_builder_restrictions)\", \"current_core_version\": \"\", \"cms_ids\": [], \"new_core_version\": \"\"}";
// The update data has to be massaged a bit as it contains escaped characters.
$escaped_data = str_replace("\u0022", "\\\"", $updates_info_raw );
$escaped_data = str_replace("\u0027", "\\'",  $escaped_data );
// The data can now be decoded and analyzed.
$updates_info = json_decode($updates_info_raw, TRUE);
$extension_list = $updates_info['extension_list'];
$number_of_extensions_updated = count($extension_list);
// Loop through and build update data as a Slack markdown list.
$update_list_markdown = "*Updates performed*\n";
foreach ($extension_list as $key => $val) {
  $update_list_markdown .= "- " . $val['title'] . ' updated from ' . $val['version'] . ' to ' . $val['update_version'] . " \n";
}

// Get other info for the message.
$wf_type = $_POST['wf_type'];
$user_email = $_POST['user_email'];
$site_id = $_POST['site_id'];
$vrt_result_url = $_POST['vrt_result_url'];
$status = $_POST['vrt_status'];
$full_vrt_url_path = "https://dashboard.pantheon.io/" . $vrt_result_url;
$site_name = $_ENV['PANTHEON_SITE_NAME'];
$site_url_and_label = 'http://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io|' . $_ENV['PANTHEON_ENVIRONMENT'];
$qs_description = $_POST['qs_description'];

// Set an emoji based on pass state - star for pass, red flag for fail
$emoji = $status == "pass" ? ":star:" : ":red-flag:";

// Construct the data for Slack message API.
$message_data = [
  "text" => "Autopilot VRT for " . $site_name,
  "blocks" => [
    [
      "type" => "section",
      "text" => [
        "type" => "mrkdwn",
        "text" => "$emoji VRT Results $status for $site_name - $full_vrt_url_path",
      ]
    ],
    [
      "type" => "section",
      "block_id" => "section2",
      "text" => [
        "type" => "mrkdwn",
        "text" => "<$full_vrt_url_path|Review VRT Results for $site_name> \n The test state was: $emoji $status $emoji."
      ],
      "accessory" => [
        "type" => "image",
        "image_url" => "https://pantheon.io/sites/all/themes/zeus/images/new-design/homepage/home-hero-webops-large.jpg",
        "alt_text" => "Pantheon image"
      ]
    ],
    [
      "type" => "section",
      "block_id" => "section3",
      "fields" => [
        [
          "type" => "mrkdwn",
          "text" => $update_list_markdown,
        ]
      ]
    ]
  ]
];

// Encode the data into JSON to send to Slack.
$message_data = json_encode($message_data);

// Define the command that will send the curl to the Slack webhook with the constructed data.
$command = "curl -s -X POST -H 'Content-type: application/json' --data '" . $message_data . "' $webook_url";

// Execute the command with var_dump, so we can see output if running terminus workflow:watch, which can help for debugging.
var_dump(shell_exec("$command 2>&1"));
