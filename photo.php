<?php

namespace MyPhoto;

class Photo {
  public function submit() {
    try {
      $this->checkImage();
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
}