<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'name' => 'Admin Perpustakaan',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Categories
        $novel = \App\Models\Category::create(['name' => 'Novel']);
        $sains = \App\Models\Category::create(['name' => 'Sains & Teknologi']);
        $sejarah = \App\Models\Category::create(['name' => 'Sejarah']);
        $filsafat = \App\Models\Category::create(['name' => 'Filsafat']);

        // Books
        \App\Models\Book::create([
            'kode_buku' => 'B001',
            'judul' => 'Laskar Pelangi',
            'category_id' => $novel->id,
            'penulis' => 'Andrea Hirata',
            'penerbit' => 'Bentang Pustaka',
            'tahun_terbit' => 2005,
            'stok' => 5
        ]);

        \App\Models\Book::create([
            'kode_buku' => 'B002',
            'judul' => 'Fisika Kuantum',
            'category_id' => $sains->id,
            'penulis' => 'Albert Einstein',
            'penerbit' => 'Science Press',
            'tahun_terbit' => 2018,
            'stok' => 3
        ]);

        \App\Models\Book::create([
            'kode_buku' => 'B003',
            'judul' => 'Dunia Sophie',
            'category_id' => $filsafat->id,
            'penulis' => 'Jostein Gaarder',
            'penerbit' => 'Mizan',
            'tahun_terbit' => 1991,
            'stok' => 2
        ]);

        $this->call(MemberSeeder::class);
    }
}
