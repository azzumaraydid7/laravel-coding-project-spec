<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileUploadResource;
use App\Models\FileUpload;
use App\Jobs\ProcessCsvFile;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;

class FileUploadController extends Controller
{
    public function index()
    {
        try {
            $products = Product::latest()->paginate(20);
            return view('uploads.index', compact('products'));
        } catch (\Throwable $e) {
            $empty = new LengthAwarePaginator([], 0, 20);
            return view('uploads.index', ['products' => $empty]);
        }
    }

    public function csv()
    {
        try {
            $uploads = FileUpload::latest()->get();
            return response()->json([
                'data' => FileUploadResource::collection($uploads)
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to fetch uploads', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate(['file' => 'required|mimes:csv,txt|max:51200']);

            $path = $request->file('file')->store('csv', 'local');

            $upload = FileUpload::create([
                'filename' => $request->file('file')->getClientOriginalName(),
                'path' => $path,
                'status' => 'pending',
            ]);

            ProcessCsvFile::dispatch($upload);

            return response()->json(['message' => 'File uploaded successfully']);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Upload failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function resume(FileUpload $upload)
    {
        try {
            if (!in_array($upload->status, ['failed', 'timeout'])) {
                return response()->json(['error' => 'Only failed or timeout jobs can be resumed.'], 422);
            }

            $upload->update(['status' => 'processing', 'message' => null]);
            ProcessCsvFile::dispatch($upload);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Resume failed', 'message' => $e->getMessage()], 500);
        }
    }
}
