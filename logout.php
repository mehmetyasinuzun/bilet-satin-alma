<?php
require_once 'config.php';

// Session temizle
session_destroy();
session_start();

header('Location: index.php');
exit;
?>
