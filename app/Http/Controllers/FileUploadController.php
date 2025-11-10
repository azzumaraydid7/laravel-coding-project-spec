<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileUploadResource;
use App\Models\FileUpload;
use App\Jobs\ProcessCsvFile;
use App\Models\Product;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(20);
        return view('uploads.index', compact('products'));
    }

    public function csv()
    {
        $uploads = FileUpload::latest()->get();

        return response()->json([
            'data' => FileUploadResource::collection($uploads)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt|max:51200']);

        $path = $request->file('file')->store('csv', 'local');

        $upload = FileUpload::create([
            'filename' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'status' => 'pending',
        ]);

        ProcessCsvFile::dispatch($upload);

        return response()->json(['message' => 'File uploaded successfully']);
    }

    public function resume(FileUpload $upload)
    {
        if (!in_array($upload->status, ['failed', 'timeout'])) {
            return response()->json(['error' => 'Only failed or timeout jobs can be resumed.'], 422);
        }

        $upload->update(['status' => 'processing', 'message' => null]);
        ProcessCsvFile::dispatch($upload);

        return response()->json(['success' => true]);
    }

    public function stop(FileUpload $upload)
    {
        if ($upload->status !== 'processing') {
            return response()->json(['error' => 'Only processing jobs can be stopped.'], 422);
        }

        $upload->update(['status' => 'stopped', 'message' => 'Job stopped']);

        return response()->json(['success' => true]);
    }
}
