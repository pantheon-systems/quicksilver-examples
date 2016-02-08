<?php

echo "Quicksilver Debuging Output";
echo "\n\n";
echo "\n========= START PAYLOAD ===========\n";
print_r($_POST);
echo "\n========== END PAYLOAD ============\n";

echo "\n------- START ENVIRONMENT ---------\n";
$env = $_ENV;
unset($env['DB_PASSWORD']);
unset($env['DRUPAL_HASH_SALT']);
print_r($env);
echo "\n-------- END ENVIRONMENT ----------\n";
