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

    public function test_member_can_search_books(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
        ]);
        $category1 = Category::create([
            'name' => 'Education',
            'slug' => 'education',
        ]);
        $category2 = Category::create([
            'name' => 'Fiction',
            'slug' => 'fiction',
        ]);

        $book1 = \App\Models\Book::create([
            'kode_buku' => 'B001',
            'judul' => 'Learn Programming PHP',
            'category_id' => $category1->id,
            'penulis' => 'Taylor Otwell',
            'penerbit' => 'Laravel Press',
            'tahun_terbit' => 2021,
            'stok' => 5,
        ]);

        $book2 = \App\Models\Book::create([
            'kode_buku' => 'B002',
            'judul' => 'Classic Novel',
            'category_id' => $category2->id,
            'penulis' => 'Jane Austen',
            'penerbit' => 'Classic Pub',
            'tahun_terbit' => 1990,
            'stok' => 2,
        ]);

        // 1. Search by title keyword partial
        $responseTitle = $this->actingAs($member)->get('/books?q=program');
        $responseTitle->assertStatus(200);
        $responseTitle->assertSee('Learn Programming PHP');
        $responseTitle->assertDontSee('Classic Novel');

        // 2. Search by author
        $responseAuthor = $this->actingAs($member)->get('/books?q=Austen');
        $responseAuthor->assertStatus(200);
        $responseAuthor->assertSee('Classic Novel');
        $responseAuthor->assertDontSee('Learn Programming PHP');

        // 3. Search by category name
        $responseCategory = $this->actingAs($member)->get('/books?q=Education');
        $responseCategory->assertStatus(200);
        $responseCategory->assertSee('Learn Programming PHP');
        $responseCategory->assertDontSee('Classic Novel');

        // 4. Search that returns empty
        $responseEmpty = $this->actingAs($member)->get('/books?q=gibberishkeyword');
        $responseEmpty->assertStatus(200);
        $responseEmpty->assertSee('No books found matching your search.');
        $responseEmpty->assertDontSee('Learn Programming PHP');
        $responseEmpty->assertDontSee('Classic Novel');
    }

    public function test_admin_can_access_print_and_pdf_reports(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $responsePrint = $this->actingAs($admin)->get('/admin/reports/print');
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('LAPORAN PEMINJAMAN BUKU');

        $responsePdf = $this->actingAs($admin)->get('/admin/reports/pdf');
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('content-type', 'application/pdf');
    }

    public function test_member_cannot_access_print_and_pdf_reports(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
        ]);

        $responsePrint = $this->actingAs($member)->get('/admin/reports/print');
        $responsePrint->assertRedirect(route('member.dashboard'));

        $responsePdf = $this->actingAs($member)->get('/admin/reports/pdf');
        $responsePdf->assertRedirect(route('member.dashboard'));
    }
}
