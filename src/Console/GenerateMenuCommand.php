<?php

namespace MenqzAdmin\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use MenqzAdmin\Admin\Auth\Database\Menu;

class GenerateMenuCommand extends Command
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:generate-menu {--dry-run : Dry run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate menu items based on registered routes.';

    /**
     * Create a new command instance.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $routes = collect($this->router->getRoutes())->filter(function (Route $route) {
            $uri = $route->uri();
            // built-in, parameterized and no-GET are ignored
            return Str::startsWith($uri, 'admin/')
                && !Str::startsWith($uri, 'admin/auth/')
                && !Str::endsWith($uri, '/create')
                && !Str::contains($uri, '{')
                && in_array('GET', $route->methods());
        })
            ->map(function (Route $route) {
                $uri = substr($route->uri(), strlen('admin/'));

                return [
                    'title' => Str::ucfirst(
                        Str::snake(str_replace('/', ' ', $uri), ' ')
                    ),
                    'uri' => $uri,
                ];
            })
            ->pluck('title', 'uri');

        $menus = Menu::all()->pluck('title', 'uri');
        // exclude exist ones
        $news = $routes->diffKeys($menus)->map(function ($item, $key) {
            return [
                'title' => $item,
                'uri'   => $key,
                'order' => 10,
                'icon'  => 'fa-list',
            ];
        })->values()->toArray();

        if (!$news) {
            $this->error('No newly registered routes found.');
        } else {
            if ($this->hasOption('dry-run') && $this->option('dry-run')) {
                $this->line('<info>The following menu items will be created</info>: ');
                $this->table(['Title', 'Uri'], array_map(function ($item) {
                    return [
                        $item['title'],
                        $item['uri'],
                    ];
                }, $news));
            } else {
                Menu::insert($news);
                $this->line('<info>Done!</info>');
            }
        }
    }
}
