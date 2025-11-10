<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between max-w-6xl mx-auto">
            <a href="{{ route('products.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div x-data="uploadManager()" x-init="loadUploads()" class="max-w-3xl mx-auto py-10">

        {{-- Upload Form --}}
        <form @submit.prevent="uploadFile" x-on:drop.prevent="handleDrop($event)" x-on:dragover.prevent="isDropping = true" x-on:dragleave.prevent="isDropping = false" :class="isDropping ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50'" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 border-2 border-dashed rounded-2xl p-6 transition-all duration-300 cursor-pointer">

            <div class="flex-1 text-center sm:text-left" @click="$refs.fileInput.click()">
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h10a4 4 0 004-4M7 10l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                    <div>
                        <p class="text-gray-700 font-medium">Drag & Drop your CSV here</p>
                        <p class="text-sm text-gray-500">or click to browse</p>
                    </div>
                </div>

                <input type="file" accept=".csv" class="hidden" x-ref="fileInput" @change="selectFile">

                <button type="button" @click="$refs.fileInput.click()" class="mt-3 text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg shadow transition cursor-pointer">
                    Choose File
                </button>

                <p x-show="fileName" class="mt-2 text-sm text-gray-600">
                    Selected: <span class="font-semibold" x-text="fileName"></span>
                </p>
            </div>

            <div class="shrink-0 self-center sm:self-auto">
                <button type="submit" :disabled="uploading" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg shadow font-medium transition cursor-pointer">
                    Upload CSV
                </button>
            </div>
        </form>

        {{-- Recent Uploads --}}
        <h2 class="text-lg font-semibold mt-10 mb-4">Recent Uploads</h2>
        <div class="overflow-x-auto bg-white shadow rounded-xl">
            <table class="w-full min-w-[700px] text-sm text-left border-collapse">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="p-3 border-b border-gray-200">Uploaded At</th>
                        <th class="p-3 border-b border-gray-200">Filename</th>
                        <th class="p-3 border-b border-gray-200 text-center">Status</th>
                        <th class="p-3 border-b border-gray-200 text-center">Action / Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in uploads" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Light row background for status -->
                            <td class="p-3">
                                <div class="font-medium" x-text="item.created_at"></div>
                                <div class="text-xs text-gray-400" x-text="item.created_at_human"></div>
                            </td>

                            <td class="p-3 truncate max-w-[250px]" x-text="item.filename"></td>

                            <td class="p-3 text-center font-semibold">
                                <span :class="{
                                    'text-green-700 bg-green-50 px-2 py-0.5 rounded-full': item.status === 'completed',
                                    'text-yellow-700 bg-yellow-50 px-2 py-0.5 rounded-full': item.status === 'processing',
                                    'text-red-700 bg-red-50 px-2 py-0.5 rounded-full': item.status === 'failed' || item.status === 'timeout'
                                }" x-text="item.status">
                                </span>
                            </td>

                            <td class="p-3 text-center space-y-1">
                                <!-- Resume Button -->
                                <template x-if="item.status === 'failed' || item.status === 'timeout'">
                                    <button class="bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700 transition" @click="resumeUpload(item.id)">
                                        Resume
                                    </button>
                                </template>

                                <!-- Stop Button -->
                                <template x-if="item.status === 'processing'">
                                    <button class="bg-red-600 text-white text-xs px-3 py-1 rounded hover:bg-red-700 transition" @click="stopUpload(item.id)">
                                        Stop
                                    </button>
                                </template>

                                <!-- Done Label -->
                                <template x-if="item.status === 'completed'">
                                    <span class="text-green-600 text-xs font-medium">Done</span>
                                </template>

                                <!-- Updated At -->
                                <div class="text-xs text-gray-400" x-text="item.updated_at_human"></div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <template x-if="uploads.length === 0">
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-400">No uploads found.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function uploadManager() {
            return {
                isDropping: false,
                uploading: false,
                file: null,
                fileName: '',
                uploads: [],

                init() {
                    this.loadUploads();

                    this.pollInterval = setInterval(() => {
                        this.loadUploads();
                    }, 1000);
                },

                selectFile(event) {
                    this.file = event.target.files[0];
                    this.fileName = this.file ? this.file.name : '';
                },

                handleDrop(event) {
                    const dt = event.dataTransfer;
                    if (dt.files.length) {
                        this.file = dt.files[0];
                        this.fileName = this.file.name;
                    }
                },

                async uploadFile() {
                    if (!this.file) {
                        Swal.fire({
                            title: 'No file selected',
                            icon: 'warning'
                        });
                        return;
                    }

                    this.uploading = true;

                    // Show processing modal
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Please wait while your CSV is being uploaded and processed.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const formData = new FormData();
                    formData.append('file', this.file);

                    try {
                        const res = await fetch('/uploads', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                        if (!res.ok) throw new Error(res.statusText);

                        const data = await res.json();

                        Swal.fire({
                            title: 'Upload Successful!',
                            text: 'Your CSV is being processed.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Refresh recent uploads from server
                        await this.loadUploads();

                        // Clear selected file so choosing the same file triggers change
                        this.file = null;
                        this.fileName = '';
                        if (this.$refs && this.$refs.fileInput) {
                            this.$refs.fileInput.value = '';
                        }

                    } catch (err) {
                        Swal.fire({
                            title: 'Error!',
                            text: err.message,
                            icon: 'error'
                        });
                    } finally {
                        this.uploading = false;
                    }
                },

                async loadUploads() {
                    try {
                        const res = await fetch('/uploads-csv');
                        if (!res.ok) throw new Error(res.statusText);
                        const payload = await res.json();
                        this.uploads = payload.data || [];

                        // Show error of latest failed upload
                        const failed = this.uploads.find(u => u.status === 'failed');
                        if (failed && failed.message) {
                            Swal.fire({
                                title: 'Upload Failed!',
                                html: `<strong>${failed.filename}</strong><br>${failed.message}`,
                                icon: 'error'
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            title: 'Error!',
                            text: err.message,
                            icon: 'error'
                        });
                    }
                },

                async resumeUpload(id) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, resume it!'
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;

                        try {
                            const res = await fetch(`/uploads/${id}/resume`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            if (res.ok) {
                                this.loadUploads();
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Failed to resume job.',
                                    icon: 'error'
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                title: 'Error!',
                                text: e.message,
                                icon: 'error'
                            });
                        }
                    })
                },

                async stopUpload(id) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to resume this job!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, stop it!'
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;

                        try {
                            const res = await fetch(`/uploads/${id}/stop`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            if (res.ok) {
                                this.loadUploads();
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Failed to stop job.',
                                    icon: 'error'
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                title: 'Error!',
                                text: e.message,
                                icon: 'error'
                            });
                        }
                    })
                }
            }
        }
    </script>
</x-app-layout>
