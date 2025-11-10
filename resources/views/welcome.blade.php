<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold">Products</h1>
            <a href="{{ route('uploads.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                Upload CSV
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto mt-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2">
            <label for="unique_key" class="text-sm">Search by Unique Key:</label>
            <input id="unique_key" name="unique_key" type="text" value="{{ request('unique_key') }}" class="border rounded px-2 py-1 text-sm" placeholder="e.g. 1472511" />
            <button type="submit" class="text-sm bg-gray-800 hover:bg-gray-900 text-white px-3 py-1.5 rounded">Search</button>
            @if (request()->filled('unique_key'))
                <a href="{{ route('products.index') }}" class="text-sm text-blue-700 hover:underline">Clear</a>
            @endif
        </form>
    </div>

    <div class="max-w-6xl mx-auto py-10">
        <div class="overflow-x-auto bg-white shadow-md rounded-xl">
            <table class="w-full text-sm text-left border-collapse min-w-[900px]">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide sticky top-0 z-10">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Unique Key</th>
                        <th class="p-3 w-[300px]">Product Info</th>
                        <th class="p-3">Images</th>
                        <th class="p-3">Color / Size</th>
                        <th class="p-3 text-right">Prices</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Meta</th>
                        <th class="p-3">Created at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <!-- Index -->
                            <td class="p-3 text-gray-500">{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>

                            <!-- Unique Key -->
                            <td class="p-3 font-mono text-gray-700">{{ $product->unique_key }}</td>

                            <!-- Product Info -->
                            <td class="p-3">
                                <div class="font-semibold text-gray-800">{{ $product->product_title }}</div>
                                <div class="text-xs text-gray-500 mb-1">{{ $product->style_number }}</div>
                                <div x-data="{ expanded: false }">
                                    <!-- Description -->
                                    <div :class="expanded ? '' : 'line-clamp-2'" class="text-gray-600 text-sm transition-all duration-200" x-text="expanded ? '{{ addslashes($product->product_description) }}' : '{{ addslashes(Str::limit($product->product_description, 100)) }}'">
                                    </div>

                                    <!-- Toggle button -->
                                    <button class="text-blue-600 text-xs mt-1 hover:underline" @click="expanded = !expanded" x-text="expanded ? 'Show Less' : 'Read More'">
                                    </button>
                                </div>
                            </td>

                            <!-- Images -->
                            <td class="p-3 grid gap-2">
                                @foreach (['thumbnail_image', 'product_image'] as $imgField)
                                    @if ($product->$imgField)
                                        <img src="{{ file_exists(public_path($product->$imgField)) ? $product->$imgField : asset('img/default.png') }}" class="h-12 w-12 rounded object-cover border" alt="{{ $imgField }}">
                                    @endif
                                @endforeach
                            </td>

                            <!-- Color / Size -->
                            <td class="p-3 space-y-1">
                                <div><span class="font-semibold">Color:</span> {{ $product->color_name }}</div>
                                <div><span class="font-semibold">Size:</span> {{ $product->size }}</div>
                                <div><span class="font-semibold">Qty:</span> {{ $product->qty }}</div>
                                <div><span class="font-semibold">Weight:</span> {{ $product->piece_weight }}</div>
                            </td>

                            <!-- Prices -->
                            <td class="p-3 text-right space-y-1">
                                <div><span class="font-semibold">Piece:</span> ${{ $product->piece_price }}</div>
                                <div><span class="font-semibold">Dozen:</span> ${{ $product->dozens_price }}</div>
                                <div><span class="font-semibold">Case:</span> ${{ $product->case_price }}</div>
                            </td>

                            <!-- Category -->
                            <td class="p-3 space-y-1">
                                <div class="font-semibold text-gray-800">{{ $product->category_name }}</div>
                                <div class="text-sm text-gray-500">{{ $product->subcategory_name }}</div>
                                <div class="text-xs text-gray-400">Mill: {{ $product->mill }}</div>
                            </td>

                            <!-- Meta -->
                            <td class="p-3 space-y-1">
                                <div><span class="font-semibold">Status:</span> {{ $product->product_status }}</div>
                                <div><span class="font-semibold">GTIN:</span> {{ $product->gtin }}</div>
                                <div><span class="font-semibold">SanMar:</span> {{ $product->sanmar_mainframe_color }}</div>
                            </td>

                            <!-- Timestamps -->
                            <td class="p-3 text-xs text-gray-400 space-y-1">
                                <div>{{ $product->created_at->format('d M Y') }}</div>
                                <div>{{ $product->created_at->format('h:i a') }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
