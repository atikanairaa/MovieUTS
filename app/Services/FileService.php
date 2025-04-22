<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Upload file gambar ke folder public/images
     */
    public function uploadImage($file, $oldFileName = null)
    {
        // Generate nama file unik
        $fileName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

        // Simpan file ke folder public/images
        $file->move(public_path('images'), $fileName);

        // Hapus file lama jika ada
        if ($oldFileName) {
            $this->deleteImage($oldFileName);
        }

        return $fileName;
    }

    /**
     * Hapus file gambar dari folder public/images
     */
    public function deleteImage($fileName)
    {
        $path = public_path('images/' . $fileName);
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
