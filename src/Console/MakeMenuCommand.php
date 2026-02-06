<?php

namespace MenqzAdmin\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeMenuCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin:make-menu
                            {title : The title of the menu}
                            {uri : The URI of the menu}
                            {--icon=fa-bars : The icon class}
                            {--parent= : The parent menu Title}
                            {--permission : Create permission for this menu}
                            {--roles=admin,master : Roles to attach permission to (comma separated)}
                            {--is-parent : Indicates if this is a parent menu (forces parent_id to 0)}
                            {--order=0 : The order of the menu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration to add a new admin menu item';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $title = $this->argument('title');
        $uri = $this->argument('uri');
        $icon = $this->option('icon');
        $parentTitle = $this->option('parent');
        $isParent = $this->option('is-parent');
        $order = $this->option('order');
        $createPermission = $this->option('permission');
        $roles = explode(',', $this->option('roles'));

        if ($isParent) {
            $parentTitle = null;
        }

        $timestamp = date('Y_m_d_His');
        $filename = $timestamp . '_create_menu_' . Str::snake($title) . '.php';

        // Prepare migration content
        $migrationContent = $this->getMigrationContent($title, $uri, $icon, $parentTitle, $order, $createPermission, $roles);

        $path = database_path('migrations/' . $filename);

        file_put_contents($path, $migrationContent);

        $this->info("Migration created successfully: {$filename}");
        $this->info("Path: {$path}");
        $this->comment("Run 'php artisan migrate' to apply changes.");
    }

    protected function getMigrationContent($title, $uri, $icon, $parentTitle, $order, $createPermission, $roles)
    {
        $rolesArray = "['" . implode("', '", $roles) . "']";
        $permissionSlug = $uri;
        $permissionName = $title;

        $permissionLogic = '';
        $downPermissionLogic = '';
        $permissionField = 'null';

        if ($createPermission) {
            $permissionField = "'$permissionSlug'";
            $permissionLogic = <<<PHP

        // Create Permission
        \$permissionId = DB::table(config('admin.database.permissions_table'))->insertGetId([
            'name' => '$permissionName',
            'slug' => '$permissionSlug',
            'http_method' => '',
            'http_path' => '/$uri*',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attach to Roles
        \$roles = DB::table(config('admin.database.roles_table'))->whereIn('slug', $rolesArray)->get();
        foreach (\$roles as \$role) {
            DB::table(config('admin.database.role_permissions_table'))->insert([
                'role_id' => \$role->id,
                'permission_id' => \$permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
PHP;

            $downPermissionLogic = <<<PHP

        // Remove Permission
        \$permission = DB::table(config('admin.database.permissions_table'))->where('slug', '$permissionSlug')->first();
        if (\$permission) {
            DB::table(config('admin.database.role_permissions_table'))->where('permission_id', \$permission->id)->delete();
            DB::table(config('admin.database.permissions_table'))->where('id', \$permission->id)->delete();
        }
PHP;
        }

        $parentTitleEscaped = $parentTitle ? addslashes($parentTitle) : '';

        // Logic to find parent ID by title
        $findParentLogic = <<<PHP
        \$parentId = 0;
        \$parentTitle = '$parentTitleEscaped';
        if (!empty(\$parentTitle)) {
            \$parent = DB::table(config('admin.database.menu_table'))->where('title', \$parentTitle)->first();
            if (\$parent) {
                \$parentId = \$parent->id;
            }
        }
PHP;

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
$findParentLogic
        \$order = $order;

        // Shift existing menus order
        DB::table(config('admin.database.menu_table'))
            ->where('order', '>=', \$order)
            ->increment('order');

        // Insert Menu
        DB::table(config('admin.database.menu_table'))->insert([
            'parent_id' => \$parentId,
            'order'     => \$order,
            'title'     => '$title',
            'icon'      => '$icon',
            'uri'       => '$uri',
            'permission'=> $permissionField,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        $permissionLogic
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
$findParentLogic
        \$order = $order;

        DB::table(config('admin.database.menu_table'))->where('uri', '$uri')->delete();

        // Shift back existing menus order
        DB::table(config('admin.database.menu_table'))
            ->where('order', '>', \$order)
            ->decrement('order');

$downPermissionLogic
    }
};
PHP;
    }
}
