<?php
$upload_dir = __DIR__ . '/../uploads';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    file_put_contents($upload_dir . '/.htaccess', "Options -Indexes\nRequire all granted");
}
?>
