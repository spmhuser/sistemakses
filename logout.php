<?php
session_name('sistem_akses');
session_start();
session_destroy();
header('Location: login.php');
exit;
