<?php
/**
 * @file
 * Admin pages for the quicksilver module.
 */

/**
 * pantheon-workflow admin page
 */
function quicksilver_admin_form() {
  $form = array();
  $api_key = variable_get('quicksilver_api_key', '');

  // Generate a key if one doesn't exist
  if ($api_key === '') {
    $api_key = quicksilver_admin_generate_key();
  }

  $form['quicksilver_api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('API Key'),
    '#value' => $api_key,
    '#disabled' => TRUE,
    '#description' => t('This API key must be present as the value for X-Auth_Key header with incoming requests.')
  );

  $json = '{
  "url": "' .  ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/quicksilver/:api_key",
  "api_key": "' . $api_key . '"
}';

  $form['example_config'] = array(
    '#type' => 'textarea',
    '#title' => t('Json config example'),
    '#value' => $json,
    '#disabled' => TRUE,
    '#description' => t('This should be the contents of your quicksilver webhook config. (files/private/webhook.json)')
  );

  $form['quicksilver_test_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Test mode'),
    '#description' => t('Test mode writes payload and environment data received to the db log.'),
    '#default_value' => variable_get('quicksilver_test_mode', FALSE)
  );

  $form['regen'] = array(
    '#type' => 'checkbox',
    '#title' => t('Generate new API key')
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit')
  );

  return $form;
}

/**
 * Implements hook_submit()
 */
function quicksilver_admin_form_submit($form, &$form_state) {
  if ($form_state['values']['regen']) {
    quicksilver_admin_generate_key();
    drupal_set_message('New API key has been generated.');
  }
  variable_set('quicksilver_test_mode', $form_state['values']['quicksilver_test_mode']);
  drupal_set_message('Settings updated successfully.');
}

/**
 * Generates random API key
 */
function quicksilver_admin_generate_key() {
  $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $max = strlen($chars) - 1;
  $key = '';
  for ($i = 0; $i < 40; $i++) {
    $key .= $chars[rand(0, $max)];
  }
  variable_set('quicksilver_api_key', $key);
  return $key;
}
