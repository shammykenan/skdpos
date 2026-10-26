<?php
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['role']);
unset($_SESSION['success']);
header('Location: index.php?page=landing');
exit;