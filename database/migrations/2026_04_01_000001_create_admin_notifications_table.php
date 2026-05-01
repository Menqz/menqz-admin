<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminNotificationsTable extends Migration
{
    public function getConnection()
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    public function up()
    {
        Schema::create(config('admin.database.notifications_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->index();
            $table->integer('role_id')->nullable()->index();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->string('icon', 100)->default('icon-bell');
            $table->text('url_redirect')->nullable();
            $table->string('title_redirect', 100)->nullable();
            $table->timestamp('viewed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'viewed_at']);
            $table->index(['role_id', 'viewed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('admin.database.notifications_table'));
    }
}

