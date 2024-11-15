<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\Category; // Import model Category
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostsController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        if ($request->has('q')) {
            $query->where('judul', 'like', '%' . $request->query('q') . '%');
        }

        $posts = $query->paginate(10); // Ambil hasil paginasi

        return view('admin.posts.index', compact('posts'));
    }

    public function show($id)
    {
        $post = Post::findOrFail($id); // Get the post or fail
        return view('admin.posts.show', compact('post')); // Pass the post to the show view
    }


    public function create()
    {
        $categories = Category::all(); // Ambil semua kategori
        return view('admin.posts.create', compact('categories'));
    }


    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'tanggal_posts' => 'required|date',
            'status' => 'required|string|in:aktif,tidak aktif', // Validasi status
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar
        ], [
            'image.max' => 'Ukuran file gambar tidak boleh lebih dari 2MB.', // Custom error message
        ]);

        // Simpan file gambar
        $path = $request->file('image')->store('posts', 'public');

        // Simpan post ke database
        Post::create([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'tanggal_posts' => $request->tanggal_posts,
            'status' => $request->status,
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'image' => $path,
        ]);

        return redirect()->route('admin.posts.index')->with('success', 'Post berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $post = Post::findOrFail($id);
        $categories = Category::all(); // Ambil semua kategori
        return view('admin.posts.edit', compact('post', 'categories')); // Kirim kategori ke view
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Validasi input, termasuk gambar jika ada
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'tanggal_posts' => 'required|date',
            'status' => 'required|string|in:aktif,tidak aktif', // Validasi status
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar baru jika ada
        ], [
            'image.max' => 'Ukuran file gambar tidak boleh lebih dari 2MB.', // Pesan error custom
        ]);

        // Simpan gambar jika ada yang diunggah
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            // Unggah gambar baru dan simpan pathnya
            $post->image = $request->file('image')->store('posts', 'public');
        }

        // Update informasi lainnya
        $post->judul = $request->judul;
        $post->isi = $request->isi;
        $post->tanggal_posts = $request->tanggal_posts;
        $post->status = $request->status; // Menggunakan status yang dipilih
        $post->category_id = $request->category_id;

        $post->save();

        return redirect()->route('admin.posts.index')->with('success', 'Post berhasil diperbarui.');
    }


    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Hapus gambar jika ada
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json(['status' => 'success', 'message' => 'Post berhasil dihapus!']);
    }
}
