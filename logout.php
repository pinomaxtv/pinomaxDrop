<?php
require 'config.php';
unset($_SESSION['pd_user_id']);
unset($_SESSION['pd_username']);
unset($_SESSION['pd_email']);
unset($_SESSION['pd_avatar']);
session_destroy();
header("Location: index.php");
exit;
