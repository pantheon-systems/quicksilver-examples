<?php
header('Content-Type: text/plain; charset=UTF-8');
//Bootstrap Drupal fully so we have access to features module
define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

//Change directory to Drupal's root (like bootstrap.inc does) so we can use
// functions from features that expect our directory to be the root
chdir(DRUPAL_ROOT);

//If the features module is enabled/installed
if (module_exists("features")){
  module_load_include('inc', 'features', 'features.export');
  features_include();

  //Get list of all features -- reset the cache so we see new/updated features
  $modules = features_get_features(NULL, TRUE);

  //Borrowed and modified from features.drush.inc
  //For every module:
  //    look through its components, find those that are overridden/need review/rebuildable
  //    add them to an array of components that will be reverted
  //    revert all necessary components
  foreach ($modules as $module => $components_needed) {
    if (($feature = features_load_feature($module, TRUE)) && module_exists($module)) {
      $components = array();

      $states = features_get_component_states(array($feature->name), FALSE);
      foreach ($states[$feature->name] as $component => $state) {
        $revertible_states = array(FEATURES_OVERRIDDEN, FEATURES_NEEDS_REVIEW, FEATURES_REBUILDABLE);
        if (in_array($state, $revertible_states) && features_hook($component, 'features_revert')) {
          $components[] = $component;
        }
      }

      if (!empty($components_needed) && is_array($components_needed)) {
        $components = array_intersect($components, $components_needed);
      }
      //Ignore modules with no components to be reverted
      if (!empty($components)) {
        foreach ($components as $component) {
          //Ignore components that are locked
          if (!features_feature_is_locked($module, $component)) {
            features_revert(array($module => array($component)));
          }
        }
      }
    }
  }
}
