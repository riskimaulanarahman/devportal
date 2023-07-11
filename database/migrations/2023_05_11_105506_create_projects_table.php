<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('devPortal_user');
            $table->integer('requestStatus')->nullable();
            $table->text('nameSystem')->nullable();
            $table->text('description')->nullable();
            $table->text('descOldSystem')->nullable();
            $table->text('descNewSystem')->nullable();
            $table->text('purpose')->nullable();
            $table->integer('priority')->nullable();
            $table->date('expectedDate')->nullable();
            $table->date('planStart')->nullable();
            $table->date('planCompleted')->nullable();
            $table->float('progress')->nullable();
            $table->integer('projectStatus')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
