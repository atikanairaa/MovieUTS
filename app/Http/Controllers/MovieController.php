<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Services\FileService;

class MovieController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {

        $movies = Movie::latest()
            ->when(request('search'), function ($query){
                $query->search(request('search'));
            })
        ->paginate(6)->withQueryString();
        return view('homepage', compact('movies'));
    }

    public function detail($id)
    {
        $movie = Movie::find($id);
        return view('detail', compact('movie'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('input', compact('categories'));
    }

    public function store(StoreMovieRequest $request)
    {
        // Ambil data yang sudah tervalidasi
        $validated = $request->validated();

        // Simpan file foto jika ada
        if ($request->hasFile('foto_sampul')) {
            $validated['foto_sampul'] = $this->fileService->uploadImage($request->file('foto_sampul'));
        }

        // Simpan data ke database
        Movie::create($validated);

        return redirect()->route('movies.index')->with('success', 'Film berhasil ditambahkan.');
    }

    public function data()
    {
        $movies = Movie::latest()->paginate(10);
        return view('data-movies', compact('movies'));
    }

    public function form_edit($id)
    {
        $movie = Movie::find($id);
        $categories = Category::all();
        return view('form-edit', compact('movie', 'categories'));
    }

    public function update(UpdateMovieRequest $request, $id)
    {
        // Ambil data film berdasarkan ID, atau gagal jika tidak ditemukan
        $movie = Movie::findOrFail($id);

        // Jika ada file yang diupload melalui 'foto_sampul'
        if ($request->hasFile('foto_sampul')) {
            // Gunakan service untuk upload + hapus foto lama
            $fileName = $this->fileService->uploadImage($request->file('foto_sampul'), $movie->foto_sampul);

            // Update data film + foto baru
            $movie->update([
                'judul' => $request->judul,
                'sinopsis' => $request->sinopsis,
                'category_id' => $request->category_id,
                'tahun' => $request->tahun,
                'pemain' => $request->pemain,
                'foto_sampul' => $fileName,
            ]);
        } else {
            // Update data film tanpa mengganti foto
            $movie->update($request->only(['judul', 'sinopsis', 'category_id', 'tahun', 'pemain']));
        }

        return redirect('/movies/data')->with('success', 'Data berhasil diperbarui');
    }

    public function delete($id)
    {
        $movie = Movie::findOrFail($id);

        // Hapus file foto sampul jika ada, melalui FileService
        $this->fileService->deleteImage($movie->foto_sampul);

        // Hapus data movie dari database
        $movie->delete();

        return redirect('/movies/data')->with('success', 'Data berhasil dihapus');
    }
}
