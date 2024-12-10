<?php

    namespace App\Packages\CachedRepositories\src;

    use App\Services\Contracts\CacheInterface;
    use Psr\SimpleCache\CacheInterface;
    use ReflectionClass;

    class CachedRepositoryServiceProvider extends \Illuminate\Support\ServiceProvider
    {
        // Todo Brandon: Brandon Need to register observers here
        // Todo Brandon: Have the base observer here to extend from so they can resolve cache
        // Todo Brandon: Also need to register repositories here
        // Todo Brandon: Maybe they register the models they have repositories for in the config
        // Todo Brandon: Throw exception if the model doesn't have an observer or something else?
        // Todo Brandon: A switch to disable registering observers in config so they can be registered manually?

        private $models = [];

        public function boot(): void
        {

        }

        public function register(): void
        {

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
