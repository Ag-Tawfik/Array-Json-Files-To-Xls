<?php
declare(strict_types=1);

// Include the script we want to test
require_once 'script.php';

// Test configuration
const TEST_FILES = [
    'en-simple.json',
    'tr-simple.json',
];

// Test results array
$testResults = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'details' => []
];

// Function to run a test case
function runTest(string $testName, callable $testFunction): void {
    global $testResults;
    $testResults['total']++;
    
    try {
        $result = $testFunction();
        $testResults['passed']++;
        $testResults['details'][] = [
            'test' => $testName,
            'status' => 'PASSED',
            'message' => $result
        ];
        echo "✅ $testName: $result\n";
    } catch (Throwable $e) {
        $testResults['failed']++;
        $testResults['details'][] = [
            'test' => $testName,
            'status' => 'FAILED',
            'message' => $e->getMessage()
        ];
        echo "❌ $testName: " . $e->getMessage() . "\n";
    }
}

// Test 1: Directory Creation
runTest('Output Directory Creation', function() {
    ensureDirectoryExists(Config::OUTPUT_DIR);
    return "Output directory created and writable";
});

// Test 2: Filename Sanitization
runTest('Filename Sanitization', function() {
    $testCases = [
        'test.json' => 'test.json',
        'test with spaces.json' => 'test_with_spaces.json',
        'test@#$%.json' => 'test____.json',
        '../test.json' => 'test.json'
    ];
    
    foreach ($testCases as $input => $expected) {
        $result = sanitizeFilename($input);
        if ($result !== $expected) {
            throw new RuntimeException("Sanitization failed for '$input': got '$result', expected '$expected'");
        }
    }
    return "All filename sanitization tests passed";
});

// Test 3: JSON Validation
runTest('JSON Validation', function() {
    $validJson = '{"test": "value"}';
    $invalidJson = '{"test": "value"';
    
    if (!isValidJson($validJson)) {
        throw new RuntimeException("Valid JSON was rejected");
    }
    
    if (isValidJson($invalidJson)) {
        throw new RuntimeException("Invalid JSON was accepted");
    }
    
    return "JSON validation working correctly";
});

// Test 4: File Processing
foreach (TEST_FILES as $file) {
    $filePath = "test_files/$file";
    $testName = "Process File: $file";
    
    runTest($testName, function() use ($filePath, $file) {
        // Create a temporary copy of the test file
        $tempFile = tempnam(sys_get_temp_dir(), 'json_test_');
        if (!copy($filePath, $tempFile)) {
            throw new RuntimeException("Failed to create temporary copy of test file");
        }
        
        // Create test file info
        $fileInfo = [
            'name' => $file,
            'type' => 'application/json',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile)
        ];
        
        // Process the file using script.php's function
        $baseName = processFile($fileInfo, 0);
        
        // Verify output
        $csvPath = Config::OUTPUT_DIR . "/$baseName.csv";
        if (!file_exists($csvPath)) {
            throw new RuntimeException("CSV file was not created");
        }
        
        // Verify CSV content
        $csvContent = file_get_contents($csvPath);
        if ($csvContent === false) {
            throw new RuntimeException("Failed to read CSV file");
        }
        
        if (empty($csvContent)) {
            throw new RuntimeException("CSV file is empty");
        }
        
        return "Successfully converted to CSV";
    });
}

// Print summary
echo "\nTest Summary:\n";
echo "Total Tests: {$testResults['total']}\n";
echo "Passed: {$testResults['passed']}\n";
echo "Failed: {$testResults['failed']}\n";

// Print detailed results
if ($testResults['failed'] > 0) {
    echo "\nFailed Tests:\n";
    foreach ($testResults['details'] as $detail) {
        if ($detail['status'] === 'FAILED') {
            echo "- {$detail['test']}: {$detail['message']}\n";
        }
    }
}

// Exit with appropriate status code
exit($testResults['failed'] > 0 ? 1 : 0); 