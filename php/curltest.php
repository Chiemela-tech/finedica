<?php
file_put_contents('avatar_debug.log', "Test log at " . date('c') . "\n", FILE_APPEND);
echo "Done";
?>