# Refactoring MovieController

Pada pembaruan ini, kita melakukan refactoring `MovieController` untuk meningkatkan kualitas kode, keterbacaan, dan kemudahan pemeliharaan sesuai best practice Laravel. Berikut rincian perubahan yang telah dilakukan.

## Ringkasan Perubahan

1. **Refactoring Validasi**  
   Memindahkan logika validasi dari controller ke class Form Request (`StoreMovieRequest` dan `UpdateMovieRequest`).

2. **Refactoring Pencarian (Search)**  
   Memindahkan logika pencarian dari controller ke Eloquent scope `scopeSearch()` di model `Movie`.

3. **Refactoring Penanganan File**  
   Mengeluarkan logika upload dan penghapusan file ke service `FileService`.

4. **Penggunaan Form Request untuk Validasi**  
   Menggunakan class Form Request daripada validasi inline di controller.

5. **Refactoring Method `delete()`**  
   Menyerahkan penghapusan gambar ke `FileService` sebelum menghapus data movie.

---

## Detail Refactoring

### 1. Refactoring Validasi

- **Sebelumnya:**  
  Validasi dijalankan langsung di method `store()` dan `update()` menggunakan `Validator::make()` atau `$request->validate()`, sehingga ada duplikasi aturan.

- **Perubahan:**  
  - Buat `StoreMovieRequest` untuk validasi saat simpan baru.  
  - Buat `UpdateMovieRequest` untuk validasi saat update.  
  - Pindahkan semua rule (judul, sinopsis, tahun, pemain, foto_sampul) ke method `rules()` di masing‑masing Form Request.

- **Manfaat:**  
  - Controller menjadi lebih ringkas.  
  - Logika validasi terpusat dan mudah diuji.  
  - Mengikuti konvensi Laravel.

---

### 2. Refactoring Pencarian (Search)

- **Sebelumnya:**  
  Filter `search` ditulis langsung di controller, memecah fokus method `index()`.

- **Perubahan:**  
  - Tambah method `scopeSearch($query, $term)` di `app/Models/Movie.php`.  
  - Ubah `index()` menjadi:  
    ```php
    $movies = Movie::latest()
        ->when(request('search'), fn($q) => $q->search(request('search')))
        ->paginate(6)
        ->withQueryString();
    ```

- **Manfaat:**  
  - `index()` lebih bersih dan mudah dipahami.  
  - Fungsi pencarian reusable di model.

---

### 3. Refactoring Penanganan File

- **Sebelumnya:**  
  Controller memanggil `move()`, `File::delete()`, dan `Str::uuid()` langsung di beberapa method.

- **Perubahan:**  
  - Buat service `App\Services\FileService` dengan method:  
    ```php
    uploadImage($file, $oldFileName = null)
    deleteImage($fileName)
    ```  
  - Controller hanya memanggil:  
    ```php
    $this->fileService->uploadImage(...)
    $this->fileService->deleteImage(...)
    ```

- **Manfaat:**  
  - Semua operasi file terpusat di satu class.  
  - Controller menjadi tipis (very thin controller).  
  - Mudah menambahkan fitur seperti resize atau cloud storage.

---

### 4. Penggunaan Form Request untuk Validasi

- **Sebelumnya:**  
  Validasi data dilakukan inline di controller.

- **Perubahan:**  
  - Gunakan `StoreMovieRequest` dan `UpdateMovieRequest` untuk seluruh validasi input.

- **Manfaat:**  
  - Controller fokus pada alur bisnis, bukan detail validasi.  
  - Logika validasi terisolasi dan reusable.

---

### 5. Refactoring Method `delete()`

- **Sebelumnya:**  
  Controller memanggil `File::delete()` secara langsung sebelum menghapus record.

- **Perubahan:**  
  ```php
  $this->fileService->deleteImage($movie->foto_sampul);
  $movie->delete();
  ```

- **Manfaat:**  
  - Konsistensi dalam pengelolaan file.
  - Penghapusan file dan record database terpisah dengan jelas.

---

## Manfaat Keseluruhan
1. Mengurangi duplikasi kode
2. Kemudahan pemeliharaan & pengujian
3. Separation of Concerns terpenuhi
4. Mematuhi prinsip SOLID dan konvensi laravel
5. Struktur kode lebih jelas dan mudah dipahami

_Credit by: Yori Adi Atma_
_Diperbarui oleh: Atika Naira_
