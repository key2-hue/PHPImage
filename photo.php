<?php



namespace MyPhoto;

require_once(__DIR__ . '/db.php');

class Photo {
  private $photoName;
  private $photoType;
  private $photoLast;

  public function submit() {
    try {
      $this->checkImage();
      $originalFile = $_FILES['image']['name'];
      $photo = $this->checkPhotoType($originalFile);
      $beforePhoto = $this->save($originalFile);
      $this->createPhoto($beforePhoto);
      $_SESSION['success'] = 'アップロードに成功しました';
    } catch(\Exception $e) {
      $_SESSION['failure'] = $e->getMessage();
      // exit;
    }
    header('Location: http://' . $_SERVER['HTTP_HOST']);
    exit;
  }

  public function getPhotos() {
    $photos = [];
    $photoAll = [];
    $dir = opendir(PHOTO_DIR);
    while (false !== ($photo = readdir($dir))) {
      if($photo === '.' || $photo === '..') {
        continue;
      }
      $photoAll[] = $photo;
      if(file_exists(THUMB_DIR . '/' . $photo)) {
        $photos[] = basename(THUMB_DIR) . '/' . $photo;
      } else {
        $photos[] = basename(PHOTO_DIR) . '/' . $photo;
      }
    }
    array_multisort($photoAll, SORT_DESC, $photos);
    return $photos;
  }

  public function judgement() {
    $success = null;
    $failure = null;
    if(isset($_SESSION['success'])) {
      $success = $_SESSION['success'];
      unset($_SESSION['success']);
    }
    if(isset($_SESSION['failure'])) {
      $failure = $_SESSION['failure'];
      unset($_SESSION['failure']);
    }
    return [$success, $failure];
  }

  private function checkImage() {
    if(!isset($_FILES['image']) || !isset($_FILES['image']['error'])) {
      throw new \Exception('画像にエラーが出ています');
    }

    switch($_FILES['image']['error']) {
      case UPLOAD_ERR_OK:
        return true;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        throw new \Exception('ファイルが大きすぎます');
      default:
        throw new \Exception('Error' . $_FILES['image']['error']);
    }
  }

  private function checkPhotoType($originalFile) {
    $this->photoType = exif_imagetype($_FILES['image']['tmp_name']);
    $originalFile = $_FILES['image']['name'];
    switch($this->photoType) {
      case IMAGETYPE_GIF:
        $this->photoLast =  '.gif';
        break;
      case IMAGETYPE_JPEG:
        $this->photoLast =  '.jpg';
        break;
      case IMAGETYPE_PNG:
        $this->photoLast = '.png';
        break;
      default:
        throw new \Exception('画像の種類が間違っています!');
    }
    $photoFirst = str_replace($this->photoLast,'', $originalFile);
    $this->photoName = $photoFirst . $this->photoLast;
  }

  private function save($originalFile) {
    $dir = 'photos/';
    $doubleImage = scandir($dir);
    foreach($doubleImage as $d) {
      if(!is_dir($d)) {
        if($originalFile === $d) {
          throw new \Exception('同じ画像は投稿できません');
        }
      }
    }
    $path = PHOTO_DIR . '/' . $this->photoName;
    $finalPath = move_uploaded_file($_FILES['image']['tmp_name'], $path);
    if($finalPath === false) {
      throw new \Exception('アップロードされていません');
    }
    return $path;
  }

  private function createPhoto($beforePhoto) {
    $photoSize = getimagesize($beforePhoto);
    $width = $photoSize[0];
    $height = $photoSize[1];
    if ($width > THUMB_WIDTH) {
      $this->createPhotoType($beforePhoto, $width, $height);
    }
  }

  private function createPhotoType($beforePhoto, $width, $height) {
    switch($this->photoType) {
      case IMAGETYPE_GIF:
        $formPhoto = imagecreatefromgif($beforePhoto);
        break;
      case IMAGETYPE_JPEG:
        $formPhoto = imagecreatefromjpeg($beforePhoto);
        break;
      case IMAGETYPE_PNG:
        $formPhoto = imagecreatefrompng($beforePhoto);
        break;
    }
    $photoHeight = round($height * THUMB_WIDTH / $width);
    $image = imagecreatetruecolor(THUMB_WIDTH, $photoHeight);
    imagecopyresampled($image, $formPhoto, 0, 0, 0, 0, THUMB_WIDTH, $photoHeight, $width, $height);

    switch($this->photoType) {
      case IMAGETYPE_GIF:
        imagegif($image, THUMB_DIR . '/' . $this->photoName);
        break;
      case IMAGETYPE_JPEG:
        imagejpeg($image, THUMB_DIR . '/' . $this->photoName);
        break;
      case IMAGETYPE_PNG:
        imagepng($image, THUMB_DIR . '/' . $this->photoName);
        break;
    }
  }
}