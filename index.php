<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON to CSV Converter</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .drop-zone {
            border: 2px dashed #cbd5e0;
            transition: all 0.3s ease;
        }
        .drop-zone.dragover {
            border-color: #4299e1;
            background-color: #ebf8ff;
        }
        .loading {
            display: none;
        }
        .loading.active {
            display: block;
        }
        .file-item {
            transition: all 0.3s ease;
        }
        .file-item:hover {
            background-color: #f3f4f6;
        }
        .progress-bar {
            width: 0%;
            transition: width 0.3s ease;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">JSON to CSV Converter</h1>
                    <button id="helpButton" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-question-circle text-xl"></i>
                    </button>
                </div>
                
                <div id="helpModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">How to Use</h3>
                            <button id="closeHelp" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="space-y-4 text-gray-600">
                            <p>1. Drag and drop your JSON files or click to browse</p>
                            <p>2. Only valid JSON files are accepted</p>
                            <p>3. Maximum file size: 5MB per file</p>
                            <p>4. Click 'Convert Files' to start the conversion</p>
                            <p>5. Download your converted CSV files</p>
                        </div>
                    </div>
                </div>

                <p class="text-gray-600 mb-6">Upload your JSON files to convert them to CSV format. Multiple files are supported.</p>
                
                <form id="uploadForm" class="space-y-4">
                    <div class="drop-zone rounded-lg p-8 text-center cursor-pointer" id="dropZone">
                        <div class="space-y-2">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-gray-600">Drag and drop your JSON files here</p>
                            <p class="text-sm text-gray-500">or</p>
                            <label class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 cursor-pointer transition-colors duration-200">
                                <i class="fas fa-folder-open mr-2"></i>
                                <span>Browse Files</span>
                                <input type="file" name="uploadedFile[]" multiple accept=".json" class="hidden">
                            </label>
                        </div>
                    </div>

                    <div class="loading flex items-center justify-center space-x-2">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                        <span class="text-gray-600">Processing files...</span>
                    </div>

                    <div id="fileList" class="space-y-2"></div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <span id="totalSize">Total size: 0 MB</span>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center" disabled>
                            <i class="fas fa-exchange-alt mr-2"></i>
                            Convert Files
                        </button>
                    </div>
                </form>

                <div id="result" class="mt-6 hidden">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-green-800">Conversion Complete!</h3>
                            <button id="downloadAll" class="text-green-600 hover:text-green-800 flex items-center">
                                <i class="fas fa-download mr-2"></i>
                                Download All
                            </button>
                        </div>
                        <p class="text-green-600 mt-2">Your files have been converted successfully.</p>
                        <div id="convertedFiles" class="mt-4 space-y-2"></div>
                    </div>
                </div>

                <div id="error" class="mt-6 hidden">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            <h3 class="text-lg font-semibold text-red-800">Error</h3>
                        </div>
                        <p id="errorMessage" class="text-red-600 mt-2"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.querySelector('input[type="file"]');
            const fileList = document.getElementById('fileList');
            const uploadForm = document.getElementById('uploadForm');
            const submitButton = document.querySelector('button[type="submit"]');
            const loading = document.querySelector('.loading');
            const result = document.getElementById('result');
            const error = document.getElementById('error');
            const convertedFiles = document.getElementById('convertedFiles');
            const errorMessage = document.getElementById('errorMessage');
            const totalSize = document.getElementById('totalSize');
            const helpButton = document.getElementById('helpButton');
            const helpModal = document.getElementById('helpModal');
            const closeHelp = document.getElementById('closeHelp');
            const downloadAll = document.getElementById('downloadAll');

            // Help modal functionality
            helpButton.addEventListener('click', () => helpModal.classList.remove('hidden'));
            closeHelp.addEventListener('click', () => helpModal.classList.add('hidden'));
            helpModal.addEventListener('click', (e) => {
                if (e.target === helpModal) helpModal.classList.add('hidden');
            });

            // Handle drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropZone.classList.add('dragover');
            }

            function unhighlight() {
                dropZone.classList.remove('dragover');
            }

            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }

            // Handle file input change
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function handleFiles(files) {
                fileList.innerHTML = '';
                let hasValidFiles = false;
                let totalSizeBytes = 0;

                Array.from(files).forEach(file => {
                    if (file.type === 'application/json' || file.name.endsWith('.json')) {
                        hasValidFiles = true;
                        totalSizeBytes += file.size;
                        
                        const fileItem = document.createElement('div');
                        fileItem.className = 'file-item flex items-center justify-between bg-gray-50 p-2 rounded';
                        fileItem.innerHTML = `
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-file-alt text-gray-400"></i>
                                <span class="text-gray-700">${file.name}</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-500">${formatFileSize(file.size)}</span>
                                <button class="text-red-500 hover:text-red-700" onclick="this.parentElement.parentElement.remove(); updateTotalSize();">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        fileList.appendChild(fileItem);
                    }
                });

                updateTotalSize();
                submitButton.disabled = !hasValidFiles;
            }

            function updateTotalSize() {
                const files = fileInput.files;
                let totalSizeBytes = 0;
                
                Array.from(files).forEach(file => {
                    if (file.type === 'application/json' || file.name.endsWith('.json')) {
                        totalSizeBytes += file.size;
                    }
                });

                totalSize.textContent = `Total size: ${formatFileSize(totalSizeBytes)}`;
            }

            // Handle form submission
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                const files = fileInput.files;
                
                for (let i = 0; i < files.length; i++) {
                    formData.append('uploadedFile[]', files[i]);
                }

                loading.classList.add('active');
                submitButton.disabled = true;
                result.classList.add('hidden');
                error.classList.add('hidden');

                fetch('script.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loading.classList.remove('active');
                    submitButton.disabled = false;

                    if (data.success) {
                        convertedFiles.innerHTML = '';
                        data.processed_files.forEach(file => {
                            const fileItem = document.createElement('div');
                            fileItem.className = 'file-item flex items-center justify-between bg-green-50 p-2 rounded';
                            fileItem.innerHTML = `
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-file-csv text-green-500"></i>
                                    <span class="text-green-700">${file}.csv</span>
                                </div>
                                <a href="Excels/${file}.csv" download class="text-green-600 hover:text-green-800 flex items-center">
                                    <i class="fas fa-download mr-2"></i>
                                    Download
                                </a>
                            `;
                            convertedFiles.appendChild(fileItem);
                        });
                        result.classList.remove('hidden');
                    } else {
                        errorMessage.textContent = data.message;
                        error.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    loading.classList.remove('active');
                    submitButton.disabled = false;
                    errorMessage.textContent = 'An error occurred while processing the files.';
                    error.classList.remove('hidden');
                });
            });

            // Download all files
            downloadAll.addEventListener('click', function() {
                const links = convertedFiles.querySelectorAll('a');
                links.forEach(link => {
                    link.click();
                });
            });
        });
    </script>
</body>

</html>