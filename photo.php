<?php

namespace MyPhoto;

class Photo {
  private $photoName;

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
    $photoType = exif_imagetype($_FILES['image']['tmp_name']);
    switch($photoType) {
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
    if 
  }
}