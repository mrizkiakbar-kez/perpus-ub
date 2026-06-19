<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('borrowings', function (Blueprint $table) {
        $table->id();

        $table->foreignId('member_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->date('borrow_date');
        $table->date('return_date');

        $table->enum('status', [
            'Dipinjam',
            'Dikembalikan'
        ]);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
