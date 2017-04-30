<?php
//define header type
header('content-type: application/json; charset=utf-8');

$images = glob('uploads/*.{jpeg,gif,png,jpg,bmp}', GLOB_BRACE);
$data = array();
foreach($images as $image)
{
  //echo $image;
  $imageDetails = pathinfo($image);
  $imageName = $imageDetails['basename'];
  $size = formatSizeUnits(filesize($image));
  $data["files"][] = array("filename" => $imageName , "size" => $size);
}

if(count($data) > 0)
{
    $data["status"] = 0;
    echo json_encode($data);
}
else
{	
	$err["status"] = array("status"=>1);
	echo json_encode($err);
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

?>