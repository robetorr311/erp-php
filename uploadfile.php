<?php
if(isset($_FILES['file']['name'])){
    $defaultFilename = "logo";
    $extension  = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $defaultBaseName = $defaultFilename . "." . $extension;

    unlink($defaultBaseName);

    $location = $defaultBaseName;
    $imageFileType = pathinfo($location,PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageFileType);

    $valid_extensions = array("jpg");
    $response = 0;

    if(in_array(strtolower($imageFileType), $valid_extensions)) {
        if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){
            $response = $location;
        }
    }

    echo $response;
    exit;
}
echo 0;
?>
