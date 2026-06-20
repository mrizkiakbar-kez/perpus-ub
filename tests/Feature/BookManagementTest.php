<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_book_create_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/books/create');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_all_dashboard_and_management_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Dashboard
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);

        // Books list
        $this->actingAs($admin)->get('/admin/books')->assertStatus(200);

        // Categories list & create
        $this->actingAs($admin)->get('/admin/categories')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/categories/create')->assertStatus(200);

        // Members list
        $this->actingAs($admin)->get('/admin/members')->assertStatus(200);

        // Borrowings list
        $this->actingAs($admin)->get('/admin/borrowings')->assertStatus(200);

        // Reports
        $this->actingAs($admin)->get('/admin/reports')->assertStatus(200);
    }

    public function test_member_cannot_access_admin_book_create_page(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
        ]);

        $response = $this->actingAs($member)->get('/admin/books/create');

        // Middleware redirects unauthorized users to member dashboard
        $response->assertRedirect(route('member.dashboard'));
    }

    public function test_member_can_view_books_list(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
        ]);

        $response = $this->actingAs($member)->get('/books');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_book_details(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $category = Category::create([
            'name' => 'Comics',
            'slug' => 'comics',
        ]);
        $book = \App\Models\Book::create([
            'kode_buku' => 'B777',
            'judul' => 'Comic Book',
            'category_id' => $category->id,
            'penulis' => 'Artist',
            'penerbit' => 'Marvel',
            'tahun_terbit' => 2022,
            'stok' => 10,
            'deskripsi' => 'This is a description',
        ]);

        $response = $this->actingAs($admin)->get("/admin/books/{$book->id}");
        $response->assertStatus(200);
        $response->assertSee('Comic Book');
        $response->assertSee('This is a description');
    }

    public function test_member_blocked_from_viewing_book_details(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
        ]);
        $category = Category::create([
            'name' => 'Comics',
            'slug' => 'comics',
        ]);
        $book = \App\Models\Book::create([
            'kode_buku' => 'B777',
            'judul' => 'Comic Book',
            'category_id' => $category->id,
            'penulis' => 'Artist',
            'penerbit' => 'Marvel',
            'tahun_terbit' => 2022,
            'stok' => 10,
            'deskripsi' => 'This is a description',
        ]);

        // Accessing detail page redirects member to catalog with error
        $response = $this->actingAs($member)->get("/books/{$book->id}");
        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('error', 'Unauthorized access.');

        // Accessing admin detail URL redirects
        $responseAdminUrl = $this->actingAs($member)->get("/admin/books/{$book->id}");
        $responseAdminUrl->assertRedirect(route('member.dashboard'));
    }
}
