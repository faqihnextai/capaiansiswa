<!-- resources/views/materi.blade.php -->
@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-center text-gray-800">Halaman Materi Pembelajaran</h1>
    <p class="text-center mt-4 text-gray-600">Di sini akan ditampilkan daftar materi pembelajaran yang relevan.</p>

    <div class="space-y-4 mt-6">
        @if(isset($materials) && $materials->count() > 0)
            @foreach($materials as $material)
                <div class="border border-gray-200 p-4 rounded-md shadow-sm bg-gray-50">
                    <p class="font-semibold text-gray-800 text-lg">{{ $material->title }}</p>
                    <p class="text-sm text-gray-600">Kelas: {{ $material->class_grade }}</p>
                    <p class="text-sm text-gray-600">Jenis: {{ ucfirst($material->asset_type) }}</p>
                    @if($material->asset_type == 'link')
                        <p class="text-sm text-gray-700 mt-1">Link: <a href="{{ $material->content }}" target="_blank" class="text-blue-500 hover:underline break-all">{{ $material->content }}</a></p>
                    @elseif($material->asset_type == 'file')
                        <p class="text-sm text-gray-700 mt-1">File: <a href="{{ asset('storage/' . $material->content) }}" target="_blank" class="text-blue-500 hover:underline">{{ basename($material->content) }}</a></p>
                    @else {{-- asset_type == 'text' --}}
                        <p class="text-sm text-gray-700 mt-1">Isi Teks: {{ $material->content }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">Diunggah: {{ $material->created_at->format('d M Y H:i') }}</p>
                </div>
            @endforeach
        @else
            <p class="text-gray-500 text-center">Belum ada materi pembelajaran yang tersedia.</p>
        @endif
    </div>
@endsection
