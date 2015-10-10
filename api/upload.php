<?php
/**
 *  
 *  Upload the image for the current session
 *  
 */
require __DIR__ . '/autoload.php';

function imagecreatefromfile($imagepath=false) {
    if(!$imagepath || !is_readable($imagepath))
		return false;
    return @imagecreatefromstring(file_get_contents($imagepath));
}

$result = [];
$result['status'] = true;
$result['uuid'] = $_SESSION['uuid'];

//get folder
$folder = $result['uuid'][0] . '/' . $result['uuid'][1] . '/' . $result['uuid'] .'/';
$full_path = "../storage/uploads/".$folder;

if(!file_exists($full_path))
	mkdir ( $full_path, 0777, true );
	
$img = imagecreatefromfile($_FILES['file']['tmp_name']);
imagealphablending($img, FALSE);
imagesavealpha($img, TRUE);
$filename = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME). '.png';

// remove whitespace if needed
if(isset($_POST['whitespace']) && $_POST['whitespace'] == true ) {
	$white = imagecolorallocate($img, 255, 255, 255);
    imagecolortransparent($img, $white);
}

imagepng($img, $full_path . $filename);
$result['filepath'] = $folder . $filename;
$result['whitespace'] = (int) $_POST['whitespace'];

header('Content-Type: application/json');
echo json_encode($result);

