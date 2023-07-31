<?php

use App\Models\Icon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIconsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('icons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Icon::create(['name' => 'activity-outline']);
        Icon::create(['name' => 'activity-circle-outline']);
        Icon::create(['name' => 'alert_triangle-outline']);
        Icon::create(['name' => 'archive-outline']);
        Icon::create(['name' => 'arrow-circle-down-outline']);
        Icon::create(['name' => 'arrow-circle-up-outline']);
        Icon::create(['name' => 'arrow-circle-left-outline']);
        Icon::create(['name' => 'arrow-circle-right-outline']);
        Icon::create(['name' => 'arrow-down-outline']);
        Icon::create(['name' => 'arrow-up-outline']);
        Icon::create(['name' => 'arrow-left-outline']);
        Icon::create(['name' => 'arrow-right-outline']);
        Icon::create(['name' => 'arrow-back-outline']);
        Icon::create(['name' => 'arrow-forward-outline']);
        Icon::create(['name' => 'arrow-ios-downward-outline']);
        Icon::create(['name' => 'arrow-ios-upward-outline']);
        Icon::create(['name' => 'arrow-ios-back-outline']);
        Icon::create(['name' => 'arrow-ios-forward-outline']);
        Icon::create(['name' => 'arrowhead-down-outline']);
        Icon::create(['name' => 'arrowhead-up-outline']);
        Icon::create(['name' => 'arrowhead-left-outline']);
        Icon::create(['name' => 'arrowhead-right-outline']);
        Icon::create(['name' => 'at-outline']);
        Icon::create(['name' => 'attach-2-outline']);
        Icon::create(['name' => 'attach-outline']);
        Icon::create(['name' => 'award-outline']);
        Icon::create(['name' => 'backspace-outline']);
        Icon::create(['name' => 'bar-chart-2-outline']);
        Icon::create(['name' => 'bar-chart-outline']);
        Icon::create(['name' => 'battery-outline']);
        Icon::create(['name' => 'behance-outline']);
        Icon::create(['name' => 'bell-off-outline']);
        Icon::create(['name' => 'bell-outline']);
        Icon::create(['name' => 'bluetooth-outline']);
        Icon::create(['name' => 'book-open-outline']);
        Icon::create(['name' => 'book-outline']);
        Icon::create(['name' => 'bookmark-outline']);
        Icon::create(['name' => 'briefcase-outline']);
        Icon::create(['name' => 'browser-outline']);
        Icon::create(['name' => 'brush-outline']);
        Icon::create(['name' => 'bulb-outline']);
        Icon::create(['name' => 'calendar-outline']);
        Icon::create(['name' => 'camera-outline']);
        Icon::create(['name' => 'cast-outline']);
        Icon::create(['name' => 'charging-outline']);
        Icon::create(['name' => 'checkmark-cirlce-2-outline']);
        Icon::create(['name' => 'checkmark-cirlce-outline']);
        Icon::create(['name' => 'checkmark-outline']);
        Icon::create(['name' => 'checkmark-square-2-outline']);
        Icon::create(['name' => 'checkmark-square-outline']);
        Icon::create(['name' => 'chevron-down-outline']);
        Icon::create(['name' => 'chevron-left-outline']);
        Icon::create(['name' => 'chevron-right-outline']);
        Icon::create(['name' => 'chevron-up-outline']);
        Icon::create(['name' => 'clipboard-outline']);
        Icon::create(['name' => 'clock-outline']);
        Icon::create(['name' => 'close-circle-outline']);
        Icon::create(['name' => 'close-outline']);
        Icon::create(['name' => 'close-square-outline']);
        Icon::create(['name' => 'cloud-download-outline']);
        Icon::create(['name' => 'cloud-upload-outline']);
        Icon::create(['name' => 'code-download-outline']);
        Icon::create(['name' => 'code-outline']);
        Icon::create(['name' => 'collapse-outline']);
        Icon::create(['name' => 'color-pallate-outline']);
        Icon::create(['name' => 'color-picker-outline']);
        Icon::create(['name' => 'compass-outline']);
        Icon::create(['name' => 'copy-outline']);
        Icon::create(['name' => 'corner-down-left-outline']);
        Icon::create(['name' => 'corner-left-right-outline']);
        Icon::create(['name' => 'corner-right-up-outline']);
        Icon::create(['name' => 'corner-up-left-outline']);
        Icon::create(['name' => 'corner-up-right-outline']);
        Icon::create(['name' => 'credit-card-outline']);
        Icon::create(['name' => 'crop-outline']);
        Icon::create(['name' => 'cube-outline']);
        Icon::create(['name' => 'diagonal-arrow-left-down-outline']);
        Icon::create(['name' => 'diagonal-arrow-left-up-outline']);
        Icon::create(['name' => 'diagonal-arrow-right-down-outline']);
        Icon::create(['name' => 'diagonal-arrow-right-up-outline']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('icons');
    }
}
