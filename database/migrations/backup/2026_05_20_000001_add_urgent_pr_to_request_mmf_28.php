<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_mmf_28', function (Blueprint $table) {
            $table->boolean('isUrgentPR')->default(0)->nullable()->after('EstimateCost');
            $table->text('urgentPRRemarks')->nullable()->after('isUrgentPR');
        });
    }

    public function down(): void
    {
        Schema::table('request_mmf_28', function (Blueprint $table) {
            $table->dropColumn(['isUrgentPR', 'urgentPRRemarks']);
        });
    }
};
