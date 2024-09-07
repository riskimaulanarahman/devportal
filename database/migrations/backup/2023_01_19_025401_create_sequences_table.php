<?php

use App\Models\Sequence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSequencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->boolean('is_active');
            $table->timestamps();
        });

        // Insert Sequence
        Sequence::create(['title' => 'Admin', 'is_active' => true, 'created_at' => now()]);
        Sequence::create(['title' => 'Dashboard', 'is_active' => true, 'created_at' => now()]);
        Sequence::create(['title' => 'Request & Approval', 'is_active' => true, 'created_at' => now()]);
        Sequence::create(['title' => 'Master', 'is_active' => true, 'created_at' => now()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sequences');
    }
}
