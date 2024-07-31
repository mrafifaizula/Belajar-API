<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Str;
use Validator;
use Storage;

class BeritaController extends Controller
{
    public function index()
    {
        $berita = Berita::with('kategori', 'tag', 'user')->latest()->get();
        return response()->json([
            'succes' => true,
            'message' => 'daftar berita',
            'data' => $berita,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|unique:beritas',
            'desc' => 'required',
            'foto' => 'required|image|mimes:png,jpg|max:2048',
            'id_kategori' => 'required',
            'tag' => 'required|array',
            'id_user' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'validasi gagal',
                'data' => $validator->errors(),
            ], 422);
        }

        try {
            $path = $request->File('foto')->store('public/berita');

            $berita = new Berita;
            $berita->judul = $request->judul;
            $berita->slug = Str::slug($request->judul);
            $berita->desc = $request->desc;
            $berita->foto = $path;
            $berita->id_user = $request->id_user;
            $berita->id_kategori = $request->id_kategori;
            $berita->save();

            //melampirkan banyak tag
            $berita->tag()->attach($request->tag);
            return response()->json([
                'succes' => true,
                'message' => 'data berhasil dibuat',
                'data' => $berita,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'terjadi kesalahan',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $berita = Berita::findOrfail($id)->with('kategori', 'tag')->first();
            return response()->json([
                'success' => true,
                'message' => 'detail berita',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'berita tidak ditemukan',
                'data' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'desc' => 'required',
            'foto' => 'nullable|image|mimes:png,jpg|max:2048',
            'id_kategori' => 'required',
            'tag' => 'required|array',
            'id_user' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'validasi gagal',
                'data' => $validator->errors(),
            ], 422);
        }

        try {
            $path = $request->File('foto')->store('public/berita');

            $berita = Berita::findOrfail($id);
            // hapus foto lama
            if ($request->hasfile('foto')) {
                Storage::delete($berita->foto);
                $path = $request->file('foto')->store('berita');
                $berita->foto = $path;
            }
            $berita->judul = $request->judul;
            $berita->slug = Str::slug($request->judul);
            $berita->desc = $request->desc;
            $berita->id_user = $request->id_user;
            $berita->id_kategori = $request->id_kategori;
            $berita->save();

            //melampirkan banyak tag
            $berita->tag()->sync($request->tag);
            return response()->json([
                'succes' => true,
                'message' => 'data berhasil dibuat',
                'data' => $berita,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'terjadi kesalahan',
                'data' => $e->getMessage(),
            ], 500);
        }

    }

    public function destroy(string $id)
    {
        try {
            $berita = Berita::findOrfail($id);
            // hapus tag berita
            $berita->tag()->detach();
            // hapus foto
            Storage::delete($berita->foto);
            $berita->delete();
            return response()->json([
                'success' => true,
                'message' => 'detail berita',
                'data' => 'berita ' . $berita->judul . ' berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'berita tidak ditemukan',
                'data' => $e->getMessage(),
            ], 404);
        }

    }
}
