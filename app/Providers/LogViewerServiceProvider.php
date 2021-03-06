<?php

/*
 * This file is part of Laravel LogViewer.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Providers;

use App\Http\Controllers\LogViewerController;
use App\Log\Data;
use App\Log\Factory;
use App\Log\Filesystem;
use App\LogViewer;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Lightgear\Asset\Asset;

/**
 * This is the log viewer service provider class.
 *
 * @author Graham Campbell <graham@mineuk.com>
 */
class LogViewerServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupPackage($this->app->asset);

        $this->setupRoutes($this->app->router);
    }

    /**
     * Setup the package.
     *
     * @param \Lightgear\Asset $asset
     *
     * @return void
     */
    protected function setupPackage(Asset $asset)
    {
        $source = realpath(__DIR__.'/../../config/logviewer.php');

        $this->publishes([$source => config_path('logviewer.php')]);

        $this->mergeConfigFrom($source, 'logviewer');

        $this->loadViewsFrom(realpath(__DIR__.'/../views'), 'logviewer');

        $asset->registerStyles(['assets/css/logviewer.css'], '', 'logviewer');
        $asset->registerScripts(['assets/js/logviewer.js'], '', 'logviewer');
    }

    /**
     * Setup the routes.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    protected function setupRoutes(Router $router)
    {
        $router->group(['namespace' => 'App\Http\Controllers'], function (Router $router) {
            require __DIR__.'/../Http/routes.php';
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLogData();
        $this->registerLogFilesystem();
        $this->registerLogFactory();

        $this->registerLogViewer();

        $this->registerLogViewerController();
    }

    /**
     * Register the log data class.
     *
     * @return void
     */
    protected function registerLogData()
    {
        $this->app->singleton('data', function () {
            return new \App\Log\Data();
        });

        $this->app->alias('data', 'App\Log\Data');
    }

    /**
     * Register the log filesystem class.
     *
     * @return void
     */
    protected function registerLogFilesystem()
    {
        $this->app->singleton('filesystem', function ($app) {
            $files = $app['files'];
            $path = $app['path.storage'].'/logs';

            return new \App\Log\Filesystem($files, $path);
        });

        $this->app->alias('filesystem', 'App\Log\Filesystem');
    }

    /**
     * Register the log factory class.
     *
     * @return void
     */
    protected function registerLogFactory()
    {
        $this->app->singleton('factory', function ($app) {
            $filesystem = $app['filesystem'];
            $levels = $app['data']->levels();

            return new \App\Log\Factory($filesystem, $levels);
        });

        $this->app->alias('factory', 'App\Log\Factory');
    }

    /**
     * Register the log data class.
     *
     * @return void
     */
    protected function registerLogViewer()
    {
        $this->app->singleton('logviewer', function ($app) {
            $factory = $app['factory'];
            $filesystem = $app['filesystem'];
            $data = $app['data'];

            return new \App\LogViewer($factory, $filesystem, $data);
        });

        $this->app->alias('logviewer', 'App\LogViewer');
    }

    /**
     * Register the log viewer controller class.
     *
     * @return void
     */
    protected function registerLogViewerController()
    {
        $this->app->bind('App\Http\Controllers\LogViewerController', function ($app) {
            $perPage = $app['config']['logviewer.per_page'];
            $middleware = $app['config']['logviewer.middleware'];

            return new \App\Http\Controllers\LogViewerController($perPage, $middleware);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'logviewer',
            'data',
            'factory',
            'filesystem',
        ];
    }
}
