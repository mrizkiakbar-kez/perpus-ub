<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Member::create([
            'kode_anggota' => 'MBR001',
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'telepon' => '081234567890',
            'alamat' => 'Jalan Contoh No. 1, Malang',
        ]);

        Member::create([
            'kode_anggota' => 'MBR002',
            'nama' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'password',
            'telepon' => '081234567891',
            'alamat' => 'Jalan Contoh No. 2, Malang',
        ]);
    }
}
