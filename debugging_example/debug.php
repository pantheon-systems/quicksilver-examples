<?php

echo "Quicksilver Debuging Output";
echo "\n\n";
echo "\n========= START PAYLOAD ===========\n";
print_r($_POST);
echo "\n========== END PAYLOAD ============\n";

echo "\n------- START ENVIRONMENT ---------\n";
passthru("printenv");
echo "\n-------- END ENVIRONMENT ----------\n";
