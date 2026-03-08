<?php
// Helper: generates a bcrypt hash for "password"
// Open this page, copy the hash, paste into your database for test users
// DELETE this file from the server when done!

$hash = password_hash('password', PASSWORD_DEFAULT);
echo '<pre style="font-family:monospace;font-size:1.2rem;padding:2rem;background:#f5f5f5">';
echo "Hash for 'password':\n\n";
echo $hash;
echo "\n\nSQL to update test users:\n\n";
echo "UPDATE users SET password='$hash' WHERE username IN ('admin','anna_hki','mikko_tre','sara_abo');";
echo '</pre>';
echo '<p style="font-family:sans-serif;color:red;padding:1rem"><strong>⚠️ Ta bort den här filen från servern när du är klar!</strong></p>';
