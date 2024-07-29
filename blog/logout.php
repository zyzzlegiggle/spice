<?php
require_once 'lib/common.php';

session_start();
logout();
redirect_exit('index.php');

echo 'error';

?>