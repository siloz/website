<?php

$path = "uploads/test/test.jpg";

$img = "uploads/test/test.jpg";

if (($img_info = getimagesize($img)) === FALSE)
  die("Image not found or not an image");


switch ($img_info[2]) {
  case IMAGETYPE_GIF  : $src = imagecreatefromgif($img);  break;
  case IMAGETYPE_JPEG : $src = imagecreatefromjpeg($img); break;
  case IMAGETYPE_PNG  : $src = imagecreatefrompng($img);  break;
  default : die("Unknown filetype");
}

$tmp = imagecreatetruecolor(350, 494);
imagecopyresampled($tmp, $src, 0, 0, intval($_POST['x']), intval($_POST['y']),
                   350, 494, intval($_POST['w']), intval($_POST['h']));


$thumb = $path . pathinfo($img, PATHINFO_FILENAME) . "_thumb";
switch ($img_info[2]) {
  case IMAGETYPE_GIF  : imagegif($tmp,  $thumb . '.gif');      break;
  case IMAGETYPE_JPEG : imagejpeg($tmp, $thumb . '.jpg', 100); break;
  case IMAGETYPE_PNG  : imagepng($tmp,  $thumb . '.png', 9);   break;
  default : die("Unknown filetype");
}

?>

works