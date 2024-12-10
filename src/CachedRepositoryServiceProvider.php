<?php

    namespace BrandonJBegle\CachedRepositories;

    use BrandonJBegle\Contracts\CacheInterface;
    use ReflectionClass;

    class CachedRepositoryServiceProvider extends \Illuminate\Support\ServiceProvider
    {
        // Todo Brandon: Brandon Need to register observers here
        // Todo Brandon: Have the base observer here to extend from so they can resolve cache
        // Todo Brandon: Also need to register repositories here
        // Todo Brandon: Maybe they register the models they have repositories for in the config
        // Todo Brandon: Throw exception if the model doesn't have an observer or something else?
        // Todo Brandon: A switch to disable registering observers in config so they can be registered manually?

        // Todo Brandon: In future, public repositories directory and create the default directories
        // Todo Brandon: Need some stubs, so we we can generate the files with a command

        private $models = [];

        public function boot(): void
        {
            if ($this->app->runningInConsole()) {
                $this->publishes([
                    __DIR__ . '/../config' => config_path(),
                ], 'cached-repositories');
            }

            $this->models = config('cached-repositories.models', []);
            if(count($this->models)){
                $this->bootObservers(...$this->models);
            }
        }

        public function register(): void
        {
            $this->mergeConfigFrom(__DIR__ . '/../config/cached-repositories.php', 'cached-repositories');

            $except = config('cached-repositories.except', []);
            $models = array_diff($this->models, $except);

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
    }
