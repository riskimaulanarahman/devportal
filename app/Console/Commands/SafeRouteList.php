<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Console\Helper\Table;

class SafeRouteList extends Command
{
    protected $signature = 'route:safe-list {controller?}';
    protected $description = 'Display a list of routes, handling errors for any broken routes and filtering by controller';

    public function handle()
    {
        $controllerFilter = $this->argument('controller');
        $routes = Route::getRoutes();
        $rows = [];

        foreach ($routes as $route) {
            try {
                $action = $route->getActionName();
                if ($controllerFilter && strpos($action, $controllerFilter) === false) {
                    continue;
                }

                $rows[] = [
                    'domain' => $route->domain(),
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $action,
                    'middleware' => implode(', ', $route->middleware()),
                ];
            } catch (\Exception $e) {
                $this->error("Error processing route: " . $route->uri());
                $this->error($e->getMessage());
            }
        }

        // Display the table
        $table = new Table($this->output);
        $table->setHeaders(['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware']);
        $table->setRows($rows);
        $table->render();
    }
}