<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ImageOptimizationException;
use App\Services\ImageOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private array $generatedImages = [];

    private array $legacyFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->generatedImages as $path) {
            $absolutePath = public_path($path);
            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
        }

        foreach ($this->legacyFiles as $path) {
            Storage::disk('public')->delete($path);
        }

        parent::tearDown();
    }

    public function test_jpeg_images_are_resized_proportionally_and_small_images_are_not_upscaled(): void
    {
        $service = app(ImageOptimizationService::class);
        $cases = [
            ['landscape.jpg', 'jpeg', 4000, 3000, 2000, 1500],
            ['portrait.jpg', 'jpeg', 3000, 4000, 1500, 2000],
            ['panorama.jpg', 'jpeg', 5000, 500, 2000, 200],
            ['small.jpg', 'jpeg', 800, 600, 800, 600],
        ];

        foreach ($cases as [$name, $format, $width, $height, $expectedWidth, $expectedHeight]) {
            $relativePath = $service->optimize($this->makeImage($name, $format, $width, $height), 'product');
            $this->generatedImages[] = $relativePath;
            $absolutePath = public_path($relativePath);
            $output = getimagesize($absolutePath);

            $this->assertSame(IMAGETYPE_WEBP, $output[2]);
            $this->assertSame($expectedWidth, $output[0]);
            $this->assertSame($expectedHeight, $output[1]);
            $this->assertLessThan(2097152, filesize($absolutePath));
            $this->assertStringStartsWith('images/product-', $relativePath);
            $this->assertStringEndsWith('.webp', $relativePath);
            $this->assertEqualsWithDelta($width / $height, $output[0] / $output[1], 0.01);
        }
    }

    public function test_png_transparency_and_existing_webp_inputs_are_reencoded_as_real_webp(): void
    {
        $service = app(ImageOptimizationService::class);
        $pngPath = $service->optimize($this->makeTransparentPng(), 'category');
        $this->generatedImages[] = $pngPath;
        $pngOutput = getimagesize(public_path($pngPath));

        $this->assertSame(IMAGETYPE_WEBP, $pngOutput[2]);
        $decoded = imagecreatefromwebp(public_path($pngPath));
        $transparentPixel = imagecolorsforindex($decoded, imagecolorat($decoded, 0, 0));
        imagedestroy($decoded);
        $this->assertGreaterThan(0, $transparentPixel['alpha']);

        $webpPath = $service->optimize($this->makeImage('existing.webp', 'webp', 320, 240), 'product');
        $this->generatedImages[] = $webpPath;
        $webpOutput = getimagesize(public_path($webpPath));

        $this->assertSame(IMAGETYPE_WEBP, $webpOutput[2]);
        $this->assertSame(320, $webpOutput[0]);
        $this->assertSame(240, $webpOutput[1]);
        $this->assertLessThan(2097152, filesize(public_path($webpPath)));
    }

    public function test_high_entropy_image_is_progressively_resized_when_encoded_output_is_oversized(): void
    {
        config(['image-optimization.max_output_bytes' => 20000]);
        config(['image-optimization.minimum_dimension' => 64]);
        $service = app(ImageOptimizationService::class);

        $path = $service->optimize($this->makeNoiseJpeg(600), 'product');
        $this->generatedImages[] = $path;
        $output = getimagesize(public_path($path));

        $this->assertSame(IMAGETYPE_WEBP, $output[2]);
        $this->assertLessThan(600, max($output[0], $output[1]));
        $this->assertLessThan(20000, filesize(public_path($path)));
    }

    public function test_corrupt_image_is_rejected_without_leaving_a_partial_output(): void
    {
        $service = app(ImageOptimizationService::class);
        $this->expectException(ImageOptimizationException::class);
        $this->expectExceptionMessage('not a valid, supported');

        $service->optimize(UploadedFile::fake()->createWithContent('corrupt.jpg', 'not an image'), 'product');
    }

    public function test_valid_jpeg_at_the_20_mib_limit_is_saved_as_webp_with_a_relative_database_path(): void
    {
        [$admin, $category] = $this->makeAdminAndCategory();
        $jpeg = $this->encodeImage('jpeg', 120, 80);
        $upload = UploadedFile::fake()->createWithContent(
            'maximum-size.jpg',
            str_pad($jpeg, 20 * 1024 * 1024, "\0")
        );

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->productData($category, $upload));
        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', '20 MB image product')->firstOrFail();
        $this->generatedImages[] = $product->image;
        $this->assertStringStartsWith('images/product-', $product->image);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'image' => $product->image]);
        $this->assertSame(IMAGETYPE_WEBP, getimagesize(public_path($product->image))[2]);
        $this->assertLessThan(2097152, filesize(public_path($product->image)));
        $this->assertSame('/'.$product->image, parse_url($product->image_url, PHP_URL_PATH));
    }

    public function test_uploads_over_20_mib_are_rejected_with_a_validation_error(): void
    {
        [$admin, $category] = $this->makeAdminAndCategory();
        $jpeg = $this->encodeImage('jpeg', 120, 80);
        $upload = UploadedFile::fake()->createWithContent(
            'too-large.jpg',
            str_pad($jpeg, (20 * 1024 * 1024) + 1, "\0")
        );

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->productData($category, $upload))
            ->assertSessionHasErrors([
                'image' => 'The image may not be larger than 20 MB.',
            ]);

        $this->assertDatabaseMissing('products', ['name' => '20 MB image product']);
    }

    public function test_product_updates_preserve_or_replace_images_safely_and_admin_is_required(): void
    {
        [$admin, $category] = $this->makeAdminAndCategory();
        $service = app(ImageOptimizationService::class);
        $oldPath = $service->optimize($this->makeImage('old.jpg', 'jpeg', 160, 120), 'product');
        $this->generatedImages[] = $oldPath;
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Existing product',
            'slug' => 'existing-product',
            'price' => 12,
            'stock_quantity' => 4,
            'image' => $oldPath,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productData($category))
            ->assertRedirect(route('admin.products.index'));
        $this->assertSame($oldPath, $product->fresh()->image);
        $this->assertFileExists(public_path($oldPath));

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productData(
                $category,
                $this->makeImage('replacement.png', 'png', 200, 150)
            ))
            ->assertRedirect(route('admin.products.index'));
        $replacement = $product->fresh()->image;
        $this->generatedImages[] = $replacement;
        $this->assertNotSame($oldPath, $replacement);
        $this->assertFileDoesNotExist(public_path($oldPath));
        $this->assertSame(IMAGETYPE_WEBP, getimagesize(public_path($replacement))[2]);

        auth()->logout();
        $existingProductCount = Product::count();
        $this->post(route('admin.products.store'), $this->productData(
            $category,
            $this->makeImage('unauthorized.jpg', 'jpeg', 100, 100)
        ))->assertRedirect();
        $this->assertSame($existingProductCount, Product::count());
    }

    public function test_replacing_an_image_keeps_it_while_another_record_still_references_it(): void
    {
        [$admin, $category] = $this->makeAdminAndCategory();
        $service = app(ImageOptimizationService::class);
        $sharedPath = $service->optimize($this->makeImage('shared.jpg', 'jpeg', 160, 120), 'product');
        $this->generatedImages[] = $sharedPath;
        $category->update(['image' => $sharedPath]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Shared image product',
            'slug' => 'shared-image-product',
            'price' => 12,
            'stock_quantity' => 4,
            'image' => $sharedPath,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productData(
                $category,
                $this->makeImage('replacement.jpg', 'jpeg', 200, 150)
            ))
            ->assertRedirect(route('admin.products.index'));

        $this->generatedImages[] = $product->fresh()->image;
        $this->assertFileExists(public_path($sharedPath));
        $this->assertSame($sharedPath, $category->fresh()->image);
    }

    public function test_database_failure_discards_the_optimized_upload(): void
    {
        [$admin, $category] = $this->makeAdminAndCategory();
        $filesBefore = glob(public_path('images/product-*.webp')) ?: [];
        Product::creating(static function (): void {
            throw new \RuntimeException('Simulated product persistence failure.');
        });
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->post(route('admin.products.store'), $this->productData(
                $category,
                $this->makeImage('database-failure.jpg', 'jpeg', 120, 80)
            ));
            $this->fail('The simulated persistence exception was not thrown.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated product persistence failure.', $exception->getMessage());
        }

        $this->assertSame($filesBefore, glob(public_path('images/product-*.webp')) ?: []);
        $this->assertSame([], glob(public_path('images/.image-*')) ?: []);
    }

    public function test_failed_image_processing_keeps_the_existing_image_and_category_upload_uses_the_same_service(): void
    {
        [$admin, $category] = $this->makeAdminAndCategory();
        $service = app(ImageOptimizationService::class);
        $oldPath = $service->optimize($this->makeImage('old-category.jpg', 'jpeg', 160, 120), 'category');
        $this->generatedImages[] = $oldPath;
        $category->update(['image' => $oldPath]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Protected image product',
            'slug' => 'protected-image-product',
            'price' => 12,
            'stock_quantity' => 4,
            'image' => $oldPath,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), $this->productData(
                $category,
                UploadedFile::fake()->createWithContent('broken.png', 'invalid image')
            ))
            ->assertSessionHasErrors('image');
        $this->assertSame($oldPath, $product->fresh()->image);
        $this->assertFileExists(public_path($oldPath));

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Uploaded category',
                'slug' => 'uploaded-category',
                'is_active' => '1',
                'image' => $this->makeTransparentPng(),
            ])
            ->assertRedirect(route('admin.categories.index'));
        $uploadedCategory = Category::where('slug', 'uploaded-category')->firstOrFail();
        $this->generatedImages[] = $uploadedCategory->image;
        $this->assertStringStartsWith('images/category-', $uploadedCategory->image);
        $this->assertSame(IMAGETYPE_WEBP, getimagesize(public_path($uploadedCategory->image))[2]);
        $this->assertLessThan(2097152, filesize(public_path($uploadedCategory->image)));
    }

    public function test_existing_public_disk_images_remain_available_without_a_storage_symlink(): void
    {
        $legacyPath = 'products/legacy-test-image.jpg';
        $this->legacyFiles[] = $legacyPath;
        Storage::disk('public')->put($legacyPath, $this->encodeImage('jpeg', 100, 80));

        $product = new Product(['image' => $legacyPath]);
        $legacyUrlPath = parse_url($product->image_url, PHP_URL_PATH);

        $this->assertStringStartsWith('/legacy-images/', $legacyUrlPath);
        $this->get($legacyUrlPath)
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    private function makeAdminAndCategory(): array
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name' => 'Image test category',
            'slug' => 'image-test-category',
            'is_active' => true,
        ]);

        return [$admin, $category];
    }

    private function productData(Category $category, ?UploadedFile $image = null): array
    {
        return array_filter([
            'name' => '20 MB image product',
            'category_id' => $category->id,
            'price' => '10.00',
            'stock_quantity' => '2',
            'is_active' => '1',
            'featured' => '0',
            'image' => $image,
        ], static fn ($value) => $value !== null);
    }

    private function makeImage(string $name, string $format, int $width, int $height): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 35, 120, 80));
        ob_start();
        match ($format) {
            'jpeg' => imagejpeg($image, null, 85),
            'png' => imagepng($image),
            'webp' => imagewebp($image, null, 82),
        };
        $content = ob_get_clean();
        imagedestroy($image);

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    private function makeTransparentPng(): UploadedFile
    {
        $image = imagecreatetruecolor(80, 60);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagealphablending($image, true);
        imagefilledrectangle($image, 20, 15, 60, 45, imagecolorallocatealpha($image, 40, 120, 80, 0));
        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return UploadedFile::fake()->createWithContent('transparent.png', $content);
    }

    private function makeNoiseJpeg(int $size): UploadedFile
    {
        $image = imagecreatetruecolor($size, $size);
        $seed = 1234567;

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $seed = (1103515245 * $seed + 12345) & 0x7FFFFFFF;
                imagesetpixel($image, $x, $y, $seed & 0xFFFFFF);
            }
        }

        ob_start();
        imagejpeg($image, null, 92);
        $content = ob_get_clean();
        imagedestroy($image);

        return UploadedFile::fake()->createWithContent('high-entropy.jpg', $content);
    }

    private function encodeImage(string $format, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 90, 140, 180));
        ob_start();
        $format === 'jpeg' ? imagejpeg($image, null, 85) : imagewebp($image, null, 82);
        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }
}
