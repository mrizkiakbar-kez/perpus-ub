<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            if (! Schema::hasColumn('borrowings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('member_id');
            }

            if (! Schema::hasColumn('borrowings', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('return_date');
            }

            if (! Schema::hasColumn('borrowings', 'returned_by')) {
                $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete()->after('returned_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            if (Schema::hasColumn('borrowings', 'returned_by')) {
                $table->dropConstrainedForeignId('returned_by');
            }

            if (Schema::hasColumn('borrowings', 'returned_at')) {
                $table->dropColumn('returned_at');
            }

            if (Schema::hasColumn('borrowings', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
