<?php
declare(strict_types=1);

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Configuration using readonly properties
readonly class Config {
    public const array ALLOWED_TYPES = [
        'application/json' => 'json',
        'text/json' => 'json'
    ];
    public const int MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    public const string OUTPUT_DIR = './Excels';
}

// Function to sanitize filename
function sanitizeFilename(string $filename): string
{
    return preg_replace(
        '/[^a-zA-Z0-9-_\.]/',
        '_',
        basename($filename)
    );
}

// Function to validate JSON
function isValidJson(string $string): bool
{
    return json_validate($string);
}

// Function to create directory if it doesn't exist
function ensureDirectoryExists(string $dir): void
{
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0755, true)) {
            throw new RuntimeException("Failed to create directory: $dir");
        }
    }
    if (!is_writable($dir)) {
        throw new RuntimeException("Directory is not writable: $dir");
    }
}

// Function to process uploaded file
function processFile(array $file, int $index): string
{
    $fileType = $file['type'];
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $fileSize = $file['size'];
    $error = $file['error'];

    // Check for upload errors using match expression
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Upload error for file $filename: " . match($error) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            default => "Unknown error ($error)"
        });
    }

    // Validate file size
    if ($fileSize > Config::MAX_FILE_SIZE) {
        throw new RuntimeException(
            "File $filename exceeds maximum size limit of " . 
            (Config::MAX_FILE_SIZE / 1024 / 1024) . "MB"
        );
    }

    // Validate file type
    if (!isset(Config::ALLOWED_TYPES[$fileType])) {
        throw new RuntimeException(
            "Invalid file type for $filename. Only JSON files are allowed."
        );
    }

    // Sanitize filename
    $safeFilename = sanitizeFilename($filename);
    $fileNameCmps = explode(".", $safeFilename);
    $baseName = $fileNameCmps[0];

    // Read and validate JSON content
    $fileContent = file_get_contents($tmp_name);
    if ($fileContent === false) {
        throw new RuntimeException("Failed to read file: $filename");
    }

    if (!isValidJson($fileContent)) {
        throw new RuntimeException("Invalid JSON format in file $filename");
    }

    $ArrayData = json_decode($fileContent, true);
    if (!is_array($ArrayData)) {
        throw new RuntimeException("JSON file $filename does not contain an array");
    }

    // Rename the first key '__EMPTY' to 'Lang' if present
    $keys = array_keys($ArrayData);
    if (isset($keys[0]) && $keys[0] === '__EMPTY') {
        $newArray = ['Lang' => $ArrayData['__EMPTY']];
        unset($ArrayData['__EMPTY']);
        $ArrayData = $newArray + $ArrayData;
    }

    // Create CSV file
    $csvPath = Config::OUTPUT_DIR . "/$baseName.csv";
    $fp = fopen($csvPath, 'w');
    if ($fp === false) {
        throw new RuntimeException("Failed to create CSV file for $filename");
    }

    try {
        foreach ($ArrayData as $key => $value) {
            if (fputcsv($fp, [$key, $value], ',', '"', '\\') === false) {
                throw new RuntimeException("Failed to write data to CSV for $filename");
            }
        }
    } finally {
        fclose($fp);
    }

    unlink($tmp_name);
    return $baseName;
}

try {
    // Check if files were uploaded
    if (!isset($_FILES['uploadedFile']) || empty($_FILES['uploadedFile']['name'][0])) {
        throw new RuntimeException('No files were uploaded');
    }

    // Ensure output directory exists and is writable
    ensureDirectoryExists(Config::OUTPUT_DIR);

    $files = $_FILES['uploadedFile'];
    $countfiles = count($files['name']);
    $processedFiles = [];

    for ($i = 0; $i < $countfiles; $i++) {
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];

        $processedFiles[] = processFile($file, $i);
    }

    // Prepare success response
    $response = [
        'success' => true,
        'message' => 'Files processed successfully',
        'processed_files' => $processedFiles
    ];

} catch (Throwable $e) {
    // Handle errors
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_THROW_ON_ERROR);
