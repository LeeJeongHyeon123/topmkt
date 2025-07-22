<?php
echo "Simple PHP Test - " . date('Y-m-d H:i:s');
echo "<br>PHP Version: " . phpversion();
echo "<br>MySQLi Available: " . (extension_loaded('mysqli') ? 'YES' : 'NO');
?>