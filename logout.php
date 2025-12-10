<?php
// logout.php
require_once 'helpers.php';
session_destroy();
redirect('login.php');
