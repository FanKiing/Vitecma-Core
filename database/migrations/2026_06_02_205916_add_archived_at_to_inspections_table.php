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
        Schema::table('inspections', function (Blueprint $table) {
            // إضافة عمود لتخزين وقت الأرشفة، مع السماح بأن يكون فارغاً (nullable)
            $table->timestamp('archived_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // في حال أردنا التراجع عن الـ Migration، نحذف العمود
            $table->dropColumn('archived_at');
        });
    }
};
