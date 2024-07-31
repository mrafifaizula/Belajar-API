<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Berita;

class kategori extends Model
{
    use HasFactory;
    
    protected $fillable = ['nama_kategori', 'slug'];
    
    public function berita()
    {
        return $this->hasMany(Berita::class, 'id_kategori');
    }
}
