<?php
  session_start();
  require_once(__DIR__ . '/functions.php');
  require_once(__DIR__ . '/db.php');
  require_once(__DIR__ . '/photo.php');

  $upload = new \MyPhoto\Photo();
  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($_POST['send'] === "create"){
      $upload->submit();
    } else {
      
    }
  }

  $photos = $upload->getPhotos();

  list($success, $failure) = $upload->judgement();
  
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>画像掲示板</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  
    <form action="<?php print $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data" id="form">
      <input type="hidden" name="send" value="create">
      <input type="hidden" name="file_size" value="">
      <input type="file" name="image" id="file">
      <input type="submit" value="upload">
    </form>
  
  <?php if(isset($success)): ?>
    <div class="flash success"><?php echo h($success); ?></div>
  <?php endif; ?>
  <?php if(isset($failure)): ?>
    <div class="flash failure"><?php echo h($failure); ?></div>
  <?php endif; ?>
  <ul>
    <?php foreach($photos as $photo): ?>
      <li>
        <a href="<?php echo h(basename(PHOTO_DIR)) . '/' . basename($photo); ?>">
          <img src="<?php echo h($photo); ?>">
        </a>
        <form action="<?php print $_SERVER['PHP_SELF']?>" method="post">
          <input type="hidden" name="send" value="delete">
          <input type="submit" value="削除する">
        </form>
      </li>
    <?php endforeach; ?>
  </ul>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="index.js"></script>
</body>
</html>