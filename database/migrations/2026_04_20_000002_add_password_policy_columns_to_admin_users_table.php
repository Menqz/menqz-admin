<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPasswordPolicyColumnsToAdminUsersTable extends Migration
{
    /**
     * @return string
     */
    public function getConnection()
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    /**
     * @return void
     */
    public function up()
    {
        $tableName = config('admin.database.users_table');
        $schema = Schema::connection($this->getConnection());

        $schema->table($tableName, function (Blueprint $table) use ($schema, $tableName) {
            if (!$schema->hasColumn($tableName, 'is_temporary_password')) {
                $table->boolean('is_temporary_password')->default(false)->after('password');
            }

            if (!$schema->hasColumn($tableName, 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('remember_token');
            }
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        $tableName = config('admin.database.users_table');
        $schema = Schema::connection($this->getConnection());

        if (!$schema->hasTable($tableName)) {
            return;
        }

        $schema->table($tableName, function (Blueprint $table) use ($schema, $tableName) {
            if ($schema->hasColumn($tableName, 'is_temporary_password')) {
                $table->dropColumn('is_temporary_password');
            }

            if ($schema->hasColumn($tableName, 'password_changed_at')) {
                $table->dropColumn('password_changed_at');
            }
        });
    }
}
