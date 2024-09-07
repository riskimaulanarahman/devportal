<?php

use App\Models\SideMenu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSideMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('side_menus', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('route')->unique();
            $table->integer('icon_id')->default(1);
            $table->integer('parent_id')->nullable();
            $table->foreignId('sequence_id')->constrained('sequences');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_parent')->default(false);
            $table->boolean('is_secondary_menu')->default(false);
            $table->boolean('must_full_title')->default(false);
            $table->timestamps();
        });

        // Admin
        SideMenu::create(['title' => 'Module', 'route' => 'module', 'icon_id' => 1, 'sequence_id' => 1, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'Role', 'route' => 'role', 'icon_id' => 1, 'sequence_id' => 1, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'Data', 'route' => 'data', 'icon_id' => 1, 'sequence_id' => 1, 'is_active' => true,'created_at' => now(), 'update_at' => null]);

        // Dashboard
        SideMenu::create(['title' => 'Dashboard OAsys', 'route' => 'dashboard_oasys', 'icon_id' => 1, 'sequence_id' => 2, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'Dashboard HR', 'route' => 'dashboard_hr', 'icon_id' => 1, 'sequence_id' => 2, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);

        // Request & Approval

        // Advance
        SideMenu::create(['title' => 'Advance', 'route' => '#advance', 'icon_id' => 1, 'sequence_id' => 3, 'is_active' => true, 'is_parent' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'Advance Approval', 'route' => 'advance_approval', 'icon_id' => 1, 'parent_id' => 6, 'sequence_id' => 3, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'Advance Report', 'route' => 'advance_report', 'icon_id' => 1, 'parent_id' => 6, 'sequence_id' => 3, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'Advance Request', 'route' => 'advance_request', 'icon_id' => 1, 'parent_id' => 6, 'sequence_id' => 3, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);

        // IT
        SideMenu::create(['title' => 'IT', 'route' => '#it', 'icon_id' => 1, 'sequence_id' => 3, 'is_active' => true, 'is_parent' => true, 'created_at' => now(),'update_at' => null]);
        SideMenu::create(['title' => 'IT Approval', 'route' => 'it_approval', 'icon_id' => 1, 'parent_id' => 10, 'sequence_id' => 3, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'IT Report', 'route' => 'it_report', 'icon_id' => 1, 'parent_id' => 10, 'sequence_id' => 3, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'IT Request', 'route' => 'it_request', 'icon_id' => 1, 'parent_id' => 10, 'sequence_id' => 3, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        
        // Secondary Menus
        SideMenu::create(['title' => 'General Report', 'route' => '#general_report', 'icon_id' => 1, 'parent_id' => 10, 'sequence_id' => 3, 'is_parent' => true, 'is_secondary_menu' => true, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'Network', 'route' => 'report_network', 'icon_id' => 1, 'parent_id' => 14, 'sequence_id' => 3, 'parent_id' => 14, 'is_secondary_menu' => true, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);
        SideMenu::create(['title' => 'Device', 'route' => 'report_device', 'icon_id' => 1, 'parent_id' => 14, 'sequence_id' => 3, 'parent_id' => 14, 'is_secondary_menu' => true, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);

        SideMenu::create(['title' => 'System Development', 'route' => 'report_development', 'parent_id' => 10, 'icon_id' => 1, 'sequence_id' => 3, 'is_parent' => true, 'is_secondary_menu' => true, 'is_active' => true, 'created_at' => now(), 'update_at' => null]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('side_menus');
    }
}