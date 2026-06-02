# File Upload Security Fixes

## Summary
Added comprehensive file upload validation and security to prevent malicious file uploads, oversized files, and invalid file types.

## Changes Made

### 1. Created FileUploadValidator Service (app/Services/FileUploadValidator.php)
New security service with the following features:

**Security Checks:**
- ✅ File size validation (configurable limits)
- ✅ MIME type validation
- ✅ File extension validation
- ✅ Image dimension validation (min/max width/height)
- ✅ Malicious content detection (PHP code, scripts, event handlers)
- ✅ Actual file type verification (not just extension)

**Default Limits:**
- Images: 5MB max, dimensions 50x50 to 8000x8000
- Videos: 50MB max
- Supported image formats: JPG, JPEG, PNG, WEBP
- Supported video formats: MP4, MPEG, MOV, AVI

**Methods:**
- `validateImage($file, $maxSizeKb)` - Validate image uploads
- `validateVideo($file, $maxSizeKb)` - Validate video uploads
- `imageRules($required, $maxSizeKb)` - Get Laravel validation rules for images
- `videoRules($required, $maxSizeKb)` - Get Laravel validation rules for videos

### 2. Updated ImageService (app/Services/ImageService.php)
- ✅ Injected FileUploadValidator dependency
- ✅ Added security validation before processing in `uploadAndOptimize()`
- ✅ Added security validation before processing in `storeSquareWebp()`

### 3. Updated PostController (app/Http/Controllers/PostController.php)
- ✅ Injected FileUploadValidator dependency
- ✅ Replaced manual validation rules with `$this->fileValidator->imageRules()`
- ✅ Replaced custom videoUploadRules() with `$this->fileValidator->videoRules()`
- ✅ Added explicit validation calls before storing files
- ✅ Applied to both store() and update() methods

### 4. Controllers Still Need Updating
The following controllers have file uploads but lack proper security validation:

**Need FileUploadValidator:**
- ⚠️ TeacherController - Teacher profile images
- ⚠️ TeacherCourseController - Course images
- ⚠️ AdminCourseController - Course images
- ⚠️ AdminQuestionController - Exam question images
- ⚠️ TeacherExamController - Exam question images
- ⚠️ ProfileController - User avatar uploads (if exists)

## How to Apply to Remaining Controllers

### Step 1: Inject FileUploadValidator
```php
use App\Services\FileUploadValidator;

public function __construct(
    private FileUploadValidator $fileValidator
) {}
```

### Step 2: Update Validation Rules
Replace this:
```php
'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp'],
```

With this:
```php
'image' => $this->fileValidator->imageRules(required: true),
```

### Step 3: Add Explicit Validation Before Storing
```php
if ($request->hasFile('image')) {
    $this->fileValidator->validateImage($request->file('image'));
    $path = $request->file('image')->store('directory', 'public');
}
```

## Security Benefits

### Before Fix:
- ❌ No file size limits enforced
- ❌ No MIME type verification
- ❌ No malicious content detection
- ❌ Only basic extension checking
- ❌ No dimension validation
- ❌ Vulnerable to file upload attacks

### After Fix:
- ✅ Strict file size limits
- ✅ MIME type verification
- ✅ Malicious content detection (PHP, scripts)
- ✅ Extension AND content validation
- ✅ Dimension validation prevents huge images
- ✅ Protected against common file upload attacks

## Attack Vectors Prevented

1. **PHP Shell Upload** - Detects and blocks PHP code in files
2. **XSS via File Upload** - Detects script tags and event handlers
3. **File Bomb** - Size limits prevent resource exhaustion
4. **Extension Spoofing** - Validates actual file content, not just extension
5. **Image Bomb** - Dimension limits prevent memory exhaustion

## Configuration

To customize limits, modify the constants in `FileUploadValidator`:
```php
private const MAX_IMAGE_SIZE_KB = 5120; // 5MB
private const MAX_VIDEO_SIZE_KB = 51200; // 50MB
```

Or pass custom limits to methods:
```php
$this->fileValidator->validateImage($file, maxSizeKb: 10240); // 10MB
$this->fileValidator->imageRules(required: true, maxSizeKb: 2048); // 2MB
```

## Environment Configuration

Add to `.env` for PHP upload limits:
```env
# PHP.ini settings (in php.ini or .htaccess)
upload_max_filesize=50M
post_max_size=50M
max_execution_time=300
memory_limit=256M
```

## Testing

Test with:
1. Oversized files (> 5MB for images, > 50MB for videos)
2. Wrong file types (.exe, .php, .txt)
3. PHP files renamed to .jpg
4. Files with embedded scripts
5. Very large dimensions (> 8000px)
6. Very small dimensions (< 50px)

## Next Steps

1. Apply FileUploadValidator to all remaining controllers
2. Add frontend file size validation (JavaScript)
3. Consider virus scanning integration (ClamAV)
4. Add file upload audit logging
5. Implement file upload rate limiting
