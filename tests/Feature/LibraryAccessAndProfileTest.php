<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Book;
use App\Models\Category;
use App\Models\Borrowing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LibraryAccessAndProfileTest extends TestCase
{
    use RefreshDatabase;

    private $memberUser;
    private $memberRecord;
    private $adminUser;
    private $book;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name' => 'Literature',
            'slug' => 'literature'
        ]);

        $this->memberUser = User::factory()->create([
            'name' => 'Sophia Member',
            'email' => 'sophia@example.com',
            'role' => 'member',
        ]);

        $this->memberRecord = Member::create([
            'kode_anggota' => 'MBR101',
            'nama' => 'Sophia Member',
            'email' => 'sophia@example.com',
            'password' => 'password',
            'telepon' => '0812233445',
            'alamat' => 'Malang',
            'role' => 'member',
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->book = Book::create([
            'kode_buku' => 'B101',
            'judul' => 'Unique Book',
            'category_id' => $category->id,
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2021,
            'stok' => 5,
        ]);
    }

    // --- 1. Borrowing with Duration Selection ---
    public function test_member_can_borrow_with_duration_selection(): void
    {
        $response = $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post("/borrow/{$this->book->id}", [
                'duration' => 14,
            ]);

        $response->assertRedirect(route('borrowings.index'));
        $this->assertEquals(4, $this->book->fresh()->stok);

        $borrowing = Borrowing::where('user_id', $this->memberUser->id)->first();
        $this->assertNotNull($borrowing);
        $this->assertEquals(now()->addDays(14)->toDateString(), $borrowing->due_date);
    }

    // --- 2. Return Book System & Late Penalty ---
    public function test_member_can_return_borrowed_book(): void
    {
        // Setup existing borrowing
        $borrowing = Borrowing::create([
            'user_id' => $this->memberUser->id,
            'book_id' => $this->book->id,
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'borrowed',
            'duration_days' => 7,
        ]);

        // Decrement stock initially to simulate active borrow
        $this->book->update(['stok' => 4]);

        $response = $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post("/borrowings/{$borrowing->id}/return");

        $response->assertRedirect(route('borrowings.index'));
        $this->assertEquals(5, $this->book->fresh()->stok); // Stock incremented

        $borrowing = $borrowing->fresh();
        $this->assertEquals('returned', $borrowing->status);
        $this->assertNotNull($borrowing->return_date);
        $this->assertEquals('Dikembalikan', $borrowing->displayStatus());
    }

    public function test_return_book_marked_as_late_if_overdue(): void
    {
        // Setup past borrowing that was due 3 days ago
        $borrowing = Borrowing::create([
            'user_id' => $this->memberUser->id,
            'book_id' => $this->book->id,
            'borrow_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDays(3)->toDateString(),
            'status' => 'borrowed',
            'duration_days' => 7,
        ]);

        // Simulate return today
        $response = $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post("/borrowings/{$borrowing->id}/return");

        $borrowing = $borrowing->fresh();
        $this->assertEquals('Terlambat (Dikembalikan)', $borrowing->displayStatus());
        $this->assertEquals(3, $borrowing->daysLate());
        $this->assertEquals(3000, $borrowing->late_penalty);
    }

    // --- 3. Admin Restrictions & Privacy ---
    public function test_admin_blocked_from_modifying_member_profiles(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->put("/admin/members/{$this->memberRecord->id}", [
                'nama' => 'Hacked Name',
                'email' => 'hacked@example.com',
            ]);

        $response->assertStatus(403);
        $this->assertEquals('Sophia Member', $this->memberRecord->fresh()->nama);
    }

    public function test_admin_member_index_masks_emails_for_privacy(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/members');

        $response->assertStatus(200);
        $response->assertSee('so***a@example.com');
        $response->assertDontSee('sophia@example.com');
    }

    // --- 4. Member Profile Management ---
    public function test_member_can_update_own_profile(): void
    {
        $response = $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post('/profile', [
                'name' => 'Sophia Updated',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('member.profile'));
        
        // Assert changes saved
        $this->assertEquals('Sophia Updated', $this->memberRecord->fresh()->nama);
        $this->assertEquals('Sophia Updated', $this->memberUser->fresh()->name);
        
        $this->assertTrue(Hash::check('newpassword123', $this->memberUser->fresh()->password));
        $this->assertTrue(Hash::check('newpassword123', $this->memberRecord->fresh()->password));
    }

    public function test_member_profile_update_validates_password_confirmation(): void
    {
        $response = $this->actingAs($this->memberUser)
            ->withSession(['member_id' => $this->memberRecord->id])
            ->post('/profile', [
                'name' => 'Sophia Updated',
                'password' => 'newpassword123',
                'password_confirmation' => 'mismatchedpwd',
            ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals('Sophia Member', $this->memberRecord->fresh()->nama);
    }
}
