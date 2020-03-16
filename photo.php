<?php



namespace MyPhoto;

require_once(__DIR__ . '/db.php');

class Photo {
  private $photoName;
  private $photoType;

  public function submit() {
    try {
      $this->checkImage();
      $photo = $this->checkPhotoType();
      $beforePhoto = $this->save($photo);
      $this->createPhoto($beforePhoto);
      
    } catch(\Exception $e) {
      echo $e->getMessage();
      exit;
    }
    header('Location: http://' . $_SERVER['HTTP_HOST']);
    exit;
  }

  public function getPhotos() {
    $photos = [];
    $photoAll = [];
    
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

  private function checkPhotoType() {
    $this->photoType = exif_imagetype($_FILES['image']['tmp_name']);
    switch($this->photoType) {
      case IMAGETYPE_GIF:
        return 'gif';
      case IMAGETYPE_JPEG:
        return 'jpeg';
      case IMAGETYPE_PNG:
        return 'png';
      default:
        throw new \Exception('画像の種類が間違っています!');
    }
  }

  private function save($photo) {
    $this->photoName = sprintf(
      '%s_%s.%s',
      time(),
      sha1(uniqid(mt_rand(), true)),
      $photo
    );
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
    var_dump($this->photoType);
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