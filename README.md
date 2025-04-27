# JSON to CSV Converter

A modern web application that converts JSON files to CSV format with a beautiful and intuitive user interface.

## Features

- 🚀 **Modern UI**: Clean, responsive design with Tailwind CSS
- 📁 **Multiple File Support**: Upload and convert multiple JSON files at once
- 🖱️ **Drag & Drop**: Easy file upload through drag and drop
- 📊 **File Preview**: Preview selected files before conversion
- ⚡ **Real-time Validation**: Instant validation of JSON files
- 📥 **Download Options**: Download individual or all converted files
- 🛡️ **Security**: Secure file handling and validation
- 📱 **Responsive**: Works on all devices and screen sizes
- 🎨 **Visual Feedback**: Clear status indicators and error messages
- ❓ **Help System**: Built-in help guide for users
- 🔄 **Safe Testing**: Test files are preserved during testing

## Requirements

- PHP 8.4.6 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/json-to-csv-converter.git
cd json-to-csv-converter
```

2. Ensure the `Excels` directory is writable:
```bash
chmod 755 Excels
```

3. Configure your web server to point to the project directory

## Usage

1. Open `index.php` in your web browser
2. Upload JSON files by:
   - Dragging and dropping files into the upload zone
   - Clicking "Browse Files" to select files
3. Review the selected files
4. Click "Convert Files" to start the conversion
5. Download the converted CSV files

## File Format Requirements

- Files must be valid JSON
- Maximum file size: 5MB per file
- Supported JSON structures:
  - Simple key-value pairs

## Testing

The project includes a comprehensive test suite to verify functionality:

1. Run the test script:
```bash
php test.php
```

2. Test files included:
   - `test_files/en-simple.json`: English language test file
   - `test_files/tr-simple.json`: Turkish language test file

3. Test Features:
   - Safe file handling (original test files are preserved)
   - Temporary file management
   - Directory creation verification
   - Filename sanitization
   - JSON validation
   - File processing and conversion
   - Output verification

4. Test Results:
   - Detailed test summary
   - Pass/fail status for each test
   - Error messages for failed tests
   - Exit code indicates overall test status

## Project Structure

```
json-to-csv-converter/
├── index.php          # Main interface
├── script.php         # Backend processing
├── test.php           # Test suite
├── test_files/        # Test JSON files
│   ├── en-simple.json # English test file
│   └── tr-simple.json # Turkish test file
└── Excels/            # Output directory for CSV files
```

## Security Features

- File type validation
- File size limits
- Secure file handling
- Input sanitization
- Error handling
- Directory traversal prevention
- Safe temporary file management
- Original file preservation

## Error Handling

The application provides clear error messages for:
- Invalid file types
- File size exceeded
- Invalid JSON format
- Upload errors
- Processing errors
- Test failures
- Directory permission issues

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please open an issue in the GitHub repository or contact the maintainers.

## Acknowledgments

- [Tailwind CSS](https://tailwindcss.com/) for the UI framework
- [Font Awesome](https://fontawesome.com/) for icons
- PHP 8.4.6 for modern features

## Version History

- 1.0.0
  - Initial release
  - Basic JSON to CSV conversion
  - Modern UI with Tailwind CSS
  - Multiple file support
  - Drag and drop functionality
  - Safe test file handling

## Future Improvements

- [ ] Add support for more file formats
- [ ] Implement batch processing
- [ ] Add file compression
- [ ] Add progress tracking
- [ ] Implement user authentication
- [ ] Add file history
- [ ] Add more test cases
