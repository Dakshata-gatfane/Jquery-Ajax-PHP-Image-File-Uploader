<?php
//define header type
header('content-type: application/json; charset=utf-8');

//check if image data Posted
if ($_POST['img_data']) {
	//get image data
    $imageData = $_POST['img_data'];
    $token = $_POST['token'];
	
	if(md5($imageData) == $token)
	{
		//initiate class
		$imgClass = new imageValidation;
		
		//define file path for image uploads
		define('UPLOAD_DIR', 'uploads/');
		define('UPLOAD_DIR_THUMBS', 'uploads/thumbnails/');
		
		//get image type
		$typePosition  = strpos($imageData, ';');
		$imageType = explode(':', substr($imageData, 0, $typePosition))[1];
		$imageExt = $imgClass->getImageExtention($imageType);
		
		//remove image mime type
		$imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
		$imageData = str_replace('data:image/png;base64,', '', $imageData);
		$imageData = str_replace('data:image/gif;base64,', '', $imageData);
		$imageData = str_replace('data:image/bmp;base64,', '', $imageData);
		$imageData = str_replace('data:image/tiff;base64,', '', $imageData);
		
		//decode image data
		$data = base64_decode($imageData);
		
		//generate unique file name
		$file_name = uniqid() . '.' . $imageExt;
		$file = UPLOAD_DIR . $file_name;
		$file_thumb = UPLOAD_DIR_THUMBS . $file_name;
		
		//save image to folder
		$success = file_put_contents($file, $data);
		
		//generate thumbnail
		$imgClass->resizeImage($file,$file_thumb,$imageType);
		
		//print output
		//echo '[{"image":"'.$file_name.'"}]';
		$size = $imgClass->formatSizeUnits(filesize(UPLOAD_DIR . $file_name));
		$a = array("image"=>$file_name,"size"=>$size);
		echo json_encode($a);
	}else
	{
		$a = array("error"=>"token mismatch");
		echo json_encode($a);
	}
	
	

}

//image validation class
class imageValidation
{
	//set image size
	public $quality = 300;
	
	function getImageExtention($imageType)
	{
		switch(strtolower($imageType)){//determine mime type
			case 'image/png': 
				return "png";
				break;
			case 'image/gif': 
				return "gif";
				break;
			case 'image/jpeg': case 'image/pjpeg': 
				return "jpg";
				break;
			case 'image/bmp': 	
				return "bmp";
				break;
			default: return "jpg";
		}
	}
	
	function resizeImage($filename,$targetPath,$imageType)
	{
	$imageQuality=$this->quality;	
		
	// Get new dimensions
	list($width, $height) = getimagesize($filename);

	$new_width=$imageQuality;
	$new_height=($height/$width)*$new_width;

	// Resample
	$image_p = imagecreatetruecolor($new_width, $new_height);

	$image = $this->createImage($imageType,$filename);
	imagealphablending( $image_p, false );
	imagesavealpha( $image_p, true );
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	if(imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height)){
			$this->saveImage($image_p, $targetPath, $imageType, $imageQuality);
		}
	}
	
	function saveImage($source, $destination, $image_type, $imageQuality){
		switch(strtolower($image_type)){//determine mime type
			case 'image/png': 
				imagepng($source, $destination); return true; //save png file
				break;
			case 'image/gif': 
				imagegif($source, $destination); return true; //save gif file
				break;	
			case 'image/jpeg': case 'image/pjpeg': 
				imagejpeg($source, $destination, $imageQuality); return true; //save jpeg file
				break;
			default: return false;
		}
	}
	
	function createImage($imageType,$filename)
	{
		switch(strtolower($imageType)){//determine mime type
			case 'image/png': 
				return imagecreatefrompng($filename);
				break;
			case 'image/gif': 
				return imagecreatefromgif($filename);
				break;
			case 'image/jpeg': case 'image/pjpeg': 
				return imagecreatefromjpeg($filename);
				break;
			default: return imagecreatefromjpeg($filename);;
		}
	}
	function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
	}
}
?>