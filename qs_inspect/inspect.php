<?php

echo "Quicksilver Payload and Environment:";
echo "\n\n";
echo "\n============ PAYLOAD ==============\n";
print_r($_POST);
echo "\n---------- ENVIRONMENT ------------\n";
passthru("printenv");
