<?php
header("Location: index.php");

$countfiles = count($_FILES['uploadedFile']['name']);

for ($i = 0; $i < $countfiles; $i++) {

    $fileType = $_FILES['uploadedFile']['type'][$i];

    $filename = $_FILES['uploadedFile']['name'][$i];

    $tmp_name = $_FILES['uploadedFile']['tmp_name'][$i];

    $fileNameCmps = explode(".", $filename);

    if ($fileType === "application/x-php") {

        $ArrayData = include "$tmp_name";
        
    } elseif ($fileType === "application/json") {

        $ArrayData = json_decode(file_get_contents($tmp_name), true);
    }

    $fp = fopen("./Excels/$fileNameCmps[0].csv", "w");

    fputcsv($fp, ['Value', 'English']);

    foreach ($ArrayData as $key => $value) {
        fputcsv($fp, [$key, $value]);
    }

    fclose($fp);

    unlink($tmp_name);
}
