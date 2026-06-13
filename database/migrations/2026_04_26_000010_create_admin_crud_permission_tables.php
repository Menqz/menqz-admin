<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection()
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    public function up(): void
    {
        Schema::create(config('admin.database.settings_table', 'admin_settings'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 190)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create(config('admin.database.crud_permissions_table', 'admin_crud_permissions'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 190);
            $table->string('resource', 190);
            $table->string('action', 20);
            $table->string('slug', 220)->unique();
            $table->timestamps();

            $table->unique(['resource', 'action']);
            $table->index(['resource', 'action']);
        });

        Schema::create(config('admin.database.role_crud_permissions_table', 'admin_role_crud_permissions'), function (Blueprint $table) {
            $table->integer('role_id');
            $table->unsignedBigInteger('crud_permission_id');
            $table->index(['role_id', 'crud_permission_id']);
            $table->timestamps();
        });

        Schema::create(config('admin.database.user_crud_permissions_table', 'admin_user_crud_permissions'), function (Blueprint $table) {
            $table->integer('user_id');
            $table->unsignedBigInteger('crud_permission_id');
            $table->index(['user_id', 'crud_permission_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('admin.database.user_crud_permissions_table', 'admin_user_crud_permissions'));
        Schema::dropIfExists(config('admin.database.role_crud_permissions_table', 'admin_role_crud_permissions'));
        Schema::dropIfExists(config('admin.database.crud_permissions_table', 'admin_crud_permissions'));
        Schema::dropIfExists(config('admin.database.settings_table', 'admin_settings'));
    }
};

