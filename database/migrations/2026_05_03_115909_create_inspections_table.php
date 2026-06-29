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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();

            $table->string('plate_number');          
            $table->string('owner_name')->nullable(); 

            $table->enum('category', [
                'VL', //(< 3500kg)
                'PL', //(> 3500kg)
            ]);

            $table->enum('status', [
                'libre',      
                'en_cours',   
                'valider',    
                'imprimer',  
            ])->default('libre');

            $table->timestamp('started_at')->nullable();

            $table->boolean('is_admin')->default(false);

            // أضفنا سلة المهملات هنا لتسجيل وقت الحذف الناعم 🗑️
            $table->softDeletes(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};