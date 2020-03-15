<?php
  require_once(__DIR__ . '/functions.php');
  require_once(__DIR__ . '/db.php');
  require_once(__DIR__ . '/photo.php');

  $upload = new \MyPhoto\Photo();
  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload->submit();
  }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>画像掲示板</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="file_size" value="">
    <input type="file" name="image">
    <input type="submit" value="upload">
  </form>
</body>
</html>