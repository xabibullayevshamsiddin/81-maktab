<?php

namespace Tests\Unit\Services;

use App\Services\FileUploadValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FileUploadValidatorTest extends TestCase
{
    private FileUploadValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new FileUploadValidator();
    }

    public function test_validates_image_successfully(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $this->validator->validateImage($file);

        $this->assertTrue(true); // No exception thrown
    }

    public function test_rejects_oversized_image(): void
    {
        $this->expectException(ValidationException::class);

        $file = UploadedFile::fake()->image('large.jpg')->size(10000); // 10MB

        $this->validator->validateImage($file);
    }

    public function test_rejects_invalid_image_extension(): void
    {
        $this->expectException(ValidationException::class);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $this->validator->validateImage($file);
    }

    public function test_rejects_image_with_small_dimensions(): void
    {
        $this->expectException(ValidationException::class);

        $file = UploadedFile::fake()->image('tiny.jpg', 20, 20);

        $this->validator->validateImage($file);
    }

    public function test_rejects_non_image_file_with_image_extension(): void
    {
        $this->expectException(ValidationException::class);

        // Create a text file disguised as an image
        $file = UploadedFile::fake()->createWithContent('fake.jpg', 'This is not an image');

        $this->validator->validateImage($file);
    }

    public function test_validates_video_successfully(): void
    {
        $file = UploadedFile::fake()->create('video.mp4', 5000, 'video/mp4');

        $this->validator->validateVideo($file);

        $this->assertTrue(true); // No exception thrown
    }

    public function test_rejects_oversized_video(): void
    {
        $this->expectException(ValidationException::class);

        $file = UploadedFile::fake()->create('large.mp4', 60000, 'video/mp4'); // 60MB

        $this->validator->validateVideo($file);
    }

    public function test_rejects_invalid_video_mime_type(): void
    {
        $this->expectException(ValidationException::class);

        $file = UploadedFile::fake()->create('document.txt', 100);

        $this->validator->validateVideo($file);
    }

    public function test_image_rules_returns_correct_validation_rules(): void
    {
        $rules = $this->validator->imageRules(required: true, maxSizeKb: 2048);

        $this->assertContains('required', $rules);
        $this->assertContains('file', $rules);
        $this->assertContains('image', $rules);
        $this->assertContains('max:2048', $rules);
    }

    public function test_image_rules_optional_with_nullable(): void
    {
        $rules = $this->validator->imageRules(required: false);

        $this->assertContains('nullable', $rules);
        $this->assertNotContains('required', $rules);
    }

    public function test_video_rules_returns_correct_validation_rules(): void
    {
        $rules = $this->validator->videoRules(required: true, maxSizeKb: 10240);

        $this->assertContains('required', $rules);
        $this->assertContains('file', $rules);
        $this->assertContains('max:10240', $rules);
    }

    public function test_get_max_sizes_returns_configuration(): void
    {
        $sizes = FileUploadValidator::getMaxSizes();

        $this->assertIsArray($sizes);
        $this->assertArrayHasKey('image', $sizes);
        $this->assertArrayHasKey('video', $sizes);
        $this->assertArrayHasKey('general', $sizes);
        
        $this->assertEquals(5120, $sizes['image']); // 5MB
        $this->assertEquals(51200, $sizes['video']); // 50MB
    }

    public function test_get_allowed_image_extensions(): void
    {
        $extensions = FileUploadValidator::getAllowedImageExtensions();

        $this->assertIsArray($extensions);
        $this->assertContains('jpg', $extensions);
        $this->assertContains('jpeg', $extensions);
        $this->assertContains('png', $extensions);
        $this->assertContains('webp', $extensions);
    }

    public function test_get_allowed_video_extensions(): void
    {
        $extensions = FileUploadValidator::getAllowedVideoExtensions();

        $this->assertIsArray($extensions);
        $this->assertContains('mp4', $extensions);
        $this->assertContains('mpeg', $extensions);
        $this->assertContains('mov', $extensions);
        $this->assertContains('avi', $extensions);
    }

    public function test_accepts_all_allowed_image_formats(): void
    {
        $formats = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($formats as $format) {
            $file = UploadedFile::fake()->image("photo.{$format}");
            
            try {
                $this->validator->validateImage($file);
                $this->assertTrue(true); // Passed
            } catch (ValidationException $e) {
                $this->fail("Format {$format} should be accepted but was rejected");
            }
        }
    }
}
