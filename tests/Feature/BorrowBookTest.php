<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowBookTest extends TestCase
{
    use RefreshDatabase;

    private $memberUser;
    private $memberRecord;
    private $book;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a Category
        $category = Category::create([
            'name' => 'Fiction',
            'slug' => 'fiction'
        ]);

        // 2. Create a User with member role
        $this->memberUser = User::factory()->create([
            'email' => 'sophia@example.com',
            'role' => 'member',
        ]);

        // 3. Create a corresponding Member record
        $this->memberRecord = Member::create([
            'kode_anggota' => 'MBR001',
            'nama' => $this->memberUser->name,
            'email' => $this->memberUser->email,
            'password' => 'password',
            'telepon' => '0812345678',
            'alamat' => 'Address',
            'role' => 'member',
        ]);

        // 4. Create a Book
        $this->book = Book::create([
            'kode_buku' => 'B001',
            'judul' => 'Test Book',
            'category_id' => $category->id,
            'penulis' => 'Author',
            'penerbit' => 'Publisher',
            'tahun_terbit' => 2020,
            'stok' => 5,
        ]);
    }

    public function test_member_can_borrow_book_successfully(): void
    {
        $response = $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post("/borrow/{$this->book->id}");

        $response->assertRedirect(route('borrowings.index'));
        
        // Assert stock decremented
        $this->assertEquals(4, $this->book->fresh()->stok);

        // Assert borrowing records exist
        $this->assertDatabaseHas('borrowings', [
            'member_id' => $this->memberRecord->id,
            'status' => 'Dipinjam',
        ]);

        $borrowing = Borrowing::where('member_id', $this->memberRecord->id)->first();
        $this->assertNotNull($borrowing);

        $this->assertDatabaseHas('borrowing_details', [
            'borrowing_id' => $borrowing->id,
            'book_id' => $this->book->id,
            'qty' => 1,
        ]);
    }

    public function test_member_cannot_borrow_same_book_twice_without_returning(): void
    {
        // First borrow
        $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post("/borrow/{$this->book->id}");

        $this->assertEquals(4, $this->book->fresh()->stok);

        // Second borrow (should fail)
        $response = $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post("/borrow/{$this->book->id}");

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Anda sudah meminjam buku ini dan belum mengembalikannya.');
        
        // Stock must still be 4
        $this->assertEquals(4, $this->book->fresh()->stok);
    }

    public function test_member_cannot_borrow_book_out_of_stock(): void
    {
        $this->book->update(['stok' => 0]);

        $response = $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post("/borrow/{$this->book->id}");

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Stok buku ini kosong.');
        $this->assertEquals(0, $this->book->fresh()->stok);
    }

    public function test_admin_cannot_access_borrow_endpoint(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post("/borrow/{$this->book->id}");

        // Admin fails IsMember check, redirected to login
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_borrow_endpoint(): void
    {
        $response = $this->post("/borrow/{$this->book->id}");

        $response->assertRedirect(route('login'));
    }
}
