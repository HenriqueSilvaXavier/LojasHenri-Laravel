<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Contracts\View\Factory; // Make sure this is imported
use App\Models\Produto;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('files', function ($app) {
            return new Filesystem();
        });
    }

    public function boot(): void{
        Paginator::viewFactoryResolver(function () {
            return $this->app->make(Factory::class);
        });

        Paginator::useBootstrapFive();
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');

        // Explicitly add the pagination namespace
        View::addNamespace('pagination', resource_path('views/vendor/pagination'));

        View::composer('*', function ($view) {
            $categorias = Produto::pluck('categoria')->filter()->unique();
            $view->with('todasCategorias', $categorias);
        });
    }
}