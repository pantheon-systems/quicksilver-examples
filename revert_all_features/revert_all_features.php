<?php
/**
 * Created by PhpStorm.
 * User: Greg
 * Date: 12/17/2015
 * Time: 1:41 PM
 */

require __DIR__ . "/code/includes/module.inc";
require __DIR__ . "/drush/includes/command.inc";

//Drupal 8
if(defined("CORE_COMPATIBILITY")){
  //If the features module is enabled
  if(\Drupal::moduleHandler()->moduleExists('features')){
    drush_invoke("features-revert-all");
  }
}
//Drupal 7 or below
else{
  //If the features module is enabled
  if (module_exists("features")) {
    drush_invoke("features-revert-all");
  }
}