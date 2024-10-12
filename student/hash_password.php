<?php
// Hash the password
$hashed_password = password_hash('Admin@123', PASSWORD_DEFAULT);
echo $hashed_password;
?>
