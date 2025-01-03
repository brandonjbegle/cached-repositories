<?php

    namespace BrandonJBegle\CachedRepositories;

    use BrandonJBegle\CachedRepositories\Contracts\CacheInterface;
    use BrandonJBegle\CachedRepositories\CacheService\LaravelCache;
    use ReflectionClass;

    class CachedRepositoryServiceProvider extends \Illuminate\Support\ServiceProvider
    {
        // Todo Brandon: Throw exception if the model doesn't have an observer or something else?
        // Todo Brandon: A switch to disable registering observers in config so they can be registered manually?

        // Todo Brandon: In future, public repositories directory and create the default directories
        // Todo Brandon: Need some stubs, so we we can generate the files with a command

        public function boot(): void
        {
            if ($this->app->runningInConsole()) {
                $this->publishes([
                    __DIR__.'/../config' => config_path(),
                ], 'cached-repositories');
            }

            $this->bootObservers(config('cached-repositories.models', []));
        }

        public function register(): void
        {
            $this->mergeConfigFrom(__DIR__.'/../config/cached-repositories.php', 'cached-repositories');

            $this->registerCacheService();

            $except = config('cached-repositories.except', []);
            $models = array_diff(config('cached-repositories.models', []), $except);

            $this->registerRepositories($models);
        }

        private function bootObservers(array $models): void
        {
            foreach ($models as $model) {
                $r = new \ReflectionClass($model);
                $instance = $r->newInstanceWithoutConstructor();
                $observer = new \ReflectionClass('App\Observers\\'.$r->getShortName().'Observer');
                $instance->observe($observer->newInstanceWithoutConstructor());
            }
        }

        private function registerRepositories(array $models): void
        {
            foreach ($models as $model) {
                $r = new \ReflectionClass($model);
                $shortName = $r->getShortName();
                $interface = 'App\Repositories\Contracts\\'.$shortName.'RepositoryInterface';
                $this->app->bind($interface, function ($app) use ($shortName) {
                    $repository = $this->modelRepositoryInstance($app, $shortName);

                    return $this->modelDecoratorInstance($repository, $shortName);
                });
            }
        }

        private function modelRepositoryInstance($app, $model): mixed
        {
            // Todo Brandon: Get this from the configuration
            $repositoryClassName = 'App\Repositories\Eloquent\\'.$model.'Repository';
            $repositoryArgs = [$app];
            $repositoryReflection = new ReflectionClass($repositoryClassName);

            return $repositoryReflection->newInstanceArgs($repositoryArgs);
        }

        private function modelDecoratorInstance($repository, $model): mixed
        {
            // Todo Brandon: Get this from the configuration in the future
            $decoratorClassName = 'App\Repositories\Decorators\\'.$model.'CacheDecorator';
            $decoratorArgs = [$repository, [lcfirst($model)], $this->app->make(CacheInterface::class)];
            $decoratorReflection = new ReflectionClass($decoratorClassName);

            return $decoratorReflection->newInstanceArgs($decoratorArgs);
        }

        protected function registerCacheService(): void
        {
            $this->app->bind(CacheInterface::class, function ($app) {
                return new LaravelCache($app['cache'], 60);
            });
        }
    }
