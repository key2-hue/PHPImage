<?php



namespace MyPhoto;

require_once(__DIR__ . '/db.php');

class Photo {
  private $photoName;
  private $photoType;
  private $photoLast;
  public $photoDir = "photos/";
  private $thumbDir = "thumbs/";

  public function submit() {
    try {
      $this->checkImage();
      $originalFile = $_FILES['image']['name'];
      $photo = $this->checkPhotoType($originalFile);
      $beforePhoto = $this->save($originalFile);
      $this->createPhoto($beforePhoto);
      $_SESSION['good'] = 'アップロードに成功しました';
    } catch(\Exception $e) {
      $_SESSION['bad'] = $e->getMessage();
    }
    header('Location: http://' . $_SERVER['HTTP_HOST']);
    exit;
  }

  public function deleteFile($deleteFile) {
    if(!empty($deleteFile)) {
      unlink($deleteFile);
      echo $deleteFile;
      $photoDelete = str_replace('thumbs/','photos/', $deleteFile);
      unlink($photoDelete);
      }
    header('Location: http://' . $_SERVER['HTTP_HOST']);
    exit;
  }
  

  public function getPhotos() {
    $photos = [];
    $photoAll = [];
    $dir = opendir($this->photoDir);
    while (false !== ($photo = readdir($dir))) {
      if($photo === '.' || $photo === '..') {
        continue;
      }
      $photoAll[] = $photo;
      if(file_exists($this->thumbDir . $photo)) {
        $photos[] = $this->thumbDir . $photo;
      } else {
        $photos[] = $this->photoDir . $photo;
      }
    }
    array_multisort($photoAll, SORT_DESC, $photos);
    return $photos;
  }

  public function judgement() {
    $good = null;
    $bad = null;
    if(isset($_SESSION['good'])) {
      $good = $_SESSION['good'];
      unset($_SESSION['good']);
    }
    if(isset($_SESSION['bad'])) {
      $bad = $_SESSION['bad'];
      unset($_SESSION['bad']);
    }
    return [$good, $bad];
  }

  private function checkImage() {
    if(isset($_FILES['image']) && isset($_FILES['image']['error'])) {
      switch($_FILES['image']['error']) {
        case UPLOAD_ERR_OK:
          return true;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new \Exception('ファイルが大きすぎます');
        default:
          throw new \Exception('Error' . $_FILES['image']['error']);
      }
    } else {
      throw new \Exception('画像にエラーが出ています');
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
    $path = $this->photoDir . $this->photoName;
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
        imagegif($image, $this->thumbDir . $this->photoName);
        break;
      case IMAGETYPE_JPEG:
        imagejpeg($image, $this->thumbDir . $this->photoName);
        break;
      case IMAGETYPE_PNG:
        imagepng($image, $this->thumbDir . $this->photoName);
        break;
    }
  }
}