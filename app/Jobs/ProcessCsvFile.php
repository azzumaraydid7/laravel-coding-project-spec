<?php

namespace App\Jobs;

use App\Models\FileUpload;
use App\Models\Product;
use League\Csv\Reader;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public FileUpload $upload;
    public $timeout = 12000;
    public $tries = 2;

    public function __construct(FileUpload $upload)
    {
        $this->upload = $upload;
    }

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $this->upload->update(['status' => 'processing']);

        try {
            $filePath = Storage::disk('local', 'csv')->path($this->upload->path);

            if (!file_exists($filePath)) {
                throw new \Exception("File not found at path: {$filePath}");
            }

            $file = new \SplFileObject($filePath);

            // Use createFromStream
            $csv = Reader::createFromFileObject($file);

            // $csv = Reader::createFromFileObject($file);
            $csv->setHeaderOffset(0); // first row as header

            $batch = [];
            $batchSize = 500;

            foreach ($csv->getRecords() as $i => $row) {
                // Normalize encoding & trim
                $row = array_map(function ($v) {
                    if ($v === null) return null;
                    return trim(mb_convert_encoding((string) $v, 'UTF-8', 'auto'));
                }, $row);

                // Skip rows without UNIQUE_KEY
                $unique = trim((string) ($row['UNIQUE_KEY'] ?? ''));
                if ($unique === '') continue;

                // Clean numeric fields
                $numericFields = ['PIECE_PRICE', 'DOZENS_PRICE', 'CASE_PRICE', 'PIECE_WEIGHT'];
                foreach ($numericFields as $field) {
                    if (isset($row[$field])) {
                        $clean = preg_replace('/[^\d.]/', '', $row[$field]);
                        $row[$field] = $clean !== '' ? $clean : null;
                    } else {
                        $row[$field] = null;
                    }
                }

                $batch[] = [
                    'unique_key' => $unique,
                    'product_title' => html_entity_decode(mb_convert_encoding(html_entity_decode($row['PRODUCT_TITLE'] ?? ''), 'UTF-8', 'Windows-1252')),
                    'product_description' => html_entity_decode(mb_convert_encoding($row['PRODUCT_DESCRIPTION'] ?? '', 'UTF-8', 'Windows-1252')),
                    'style_number' => $row['STYLE#'] ?? null,
                    'available_sizes' => $row['AVAILABLE_SIZES'] ?? null,
                    'brand_logo_image' => $row['BRAND_LOGO_IMAGE'] ?? null,
                    'thumbnail_image' => $row['THUMBNAIL_IMAGE'] ?? null,
                    'color_swatch_image' => $row['COLOR_SWATCH_IMAGE'] ?? null,
                    'product_image' => $row['PRODUCT_IMAGE'] ?? null,
                    'spec_sheet' => $row['SPEC_SHEET'] ?? null,
                    'price_text' => $row['PRICE_TEXT'] ?? null,
                    'suggested_price' => $row['SUGGESTED_PRICE'] ?? null,
                    'category_name' => $row['CATEGORY_NAME'] ?? null,
                    'subcategory_name' => $row['SUBCATEGORY_NAME'] ?? null,
                    'color_name' => $row['COLOR_NAME'] ?? null,
                    'color_square_image' => $row['COLOR_SQUARE_IMAGE'] ?? null,
                    'color_product_image' => $row['COLOR_PRODUCT_IMAGE'] ?? null,
                    'color_product_image_thumbnail' => $row['COLOR_PRODUCT_IMAGE_THUMBNAIL'] ?? null,
                    'size' => $row['SIZE'] ?? null,
                    'qty' => isset($row['QTY']) ? (int) $row['QTY'] : null,
                    'piece_weight' => $row['PIECE_WEIGHT'],
                    'piece_price' => $row['PIECE_PRICE'],
                    'dozens_price' => $row['DOZENS_PRICE'],
                    'case_price' => $row['CASE_PRICE'],
                    'price_group' => $row['PRICE_GROUP'] ?? null,
                    'case_size' => isset($row['CASE_SIZE']) ? (int) $row['CASE_SIZE'] : null,
                    'inventory_key' => $row['INVENTORY_KEY'] ?? null,
                    'size_index' => isset($row['SIZE_INDEX']) ? (int) $row['SIZE_INDEX'] : null,
                    'sanmar_mainframe_color' => $row['SANMAR_MAINFRAME_COLOR'] ?? null,
                    'mill' => $row['MILL'] ?? null,
                    'product_status' => $row['PRODUCT_STATUS'] ?? null,
                    'companion_styles' => $row['COMPANION_STYLES'] ?? null,
                    'msrp' => $row['MSRP'] ?? null,
                    'map_pricing' => $row['MAP_PRICING'] ?? null,
                    'front_model_image_url' => $row['FRONT_MODEL_IMAGE_URL'] ?? null,
                    'back_model_image' => $row['BACK_MODEL_IMAGE'] ?? null,
                    'front_flat_image' => $row['FRONT_FLAT_IMAGE'] ?? null,
                    'back_flat_image' => $row['BACK_FLAT_IMAGE'] ?? null,
                    'product_measurements' => $row['PRODUCT_MEASUREMENTS'] ?? null,
                    'pms_color' => $row['PMS_COLOR'] ?? null,
                    'gtin' => isset($row['GTIN']) ? (string) $row['GTIN'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Batch insert every 500 records
                if (($i + 1) % $batchSize === 0) {
                    Product::upsert(
                        $batch,
                        ['unique_key'],
                        [
                            'product_title',
                            'product_description',
                            'style_number',
                            'available_sizes',
                            'brand_logo_image',
                            'thumbnail_image',
                            'color_swatch_image',
                            'product_image',
                            'spec_sheet',
                            'price_text',
                            'suggested_price',
                            'category_name',
                            'subcategory_name',
                            'color_name',
                            'color_square_image',
                            'color_product_image',
                            'color_product_image_thumbnail',
                            'size',
                            'qty',
                            'piece_weight',
                            'piece_price',
                            'dozens_price',
                            'case_price',
                            'price_group',
                            'case_size',
                            'inventory_key',
                            'size_index',
                            'sanmar_mainframe_color',
                            'mill',
                            'product_status',
                            'companion_styles',
                            'msrp',
                            'map_pricing',
                            'front_model_image_url',
                            'back_model_image',
                            'front_flat_image',
                            'back_flat_image',
                            'product_measurements',
                            'pms_color',
                            'gtin',
                            'updated_at'
                        ]
                    );
                    $batch = [];
                    gc_collect_cycles();
                }
            }

            // Insert remaining records
            if (!empty($batch)) {
                Product::upsert(
                    $batch,
                    ['unique_key'],
                    [
                        'product_title',
                        'product_description',
                        'style_number',
                        'available_sizes',
                        'brand_logo_image',
                        'thumbnail_image',
                        'color_swatch_image',
                        'product_image',
                        'spec_sheet',
                        'price_text',
                        'suggested_price',
                        'category_name',
                        'subcategory_name',
                        'color_name',
                        'color_square_image',
                        'color_product_image',
                        'color_product_image_thumbnail',
                        'size',
                        'qty',
                        'piece_weight',
                        'piece_price',
                        'dozens_price',
                        'case_price',
                        'price_group',
                        'case_size',
                        'inventory_key',
                        'size_index',
                        'sanmar_mainframe_color',
                        'mill',
                        'product_status',
                        'companion_styles',
                        'msrp',
                        'map_pricing',
                        'front_model_image_url',
                        'back_model_image',
                        'front_flat_image',
                        'back_flat_image',
                        'product_measurements',
                        'pms_color',
                        'gtin',
                        'updated_at'
                    ]
                );
            }

            $this->upload->update(['status' => 'completed']);
        } catch (\Throwable $e) {
            // Update upload status and store message
            $this->upload->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            // Log full error details
            Log::error('CSV Processing Error', [
                'upload_id' => $this->upload->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $errorMessage = $exception->getMessage();

        // Detect timeout errors
        if (str_contains(strtolower($errorMessage), 'timeout')) {
            $this->upload->update([
                'status' => 'timeout',
                'message' => 'The import process exceeded the allowed time limit.',
            ]);
        } else {
            $this->upload->update([
                'status' => 'failed',
                'message' => $errorMessage,
            ]);
        }

        Log::error('CSV Job Failed', [
            'upload_id' => $this->upload->id,
            'message' => $errorMessage,
        ]);
    }
}
