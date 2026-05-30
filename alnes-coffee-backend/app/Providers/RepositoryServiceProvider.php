<?php

namespace App\Providers;

use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PromoRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PromoRepository;
use App\Repositories\TableRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class,  ProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class,    OrderRepository::class);
        $this->app->bind(TableRepositoryInterface::class,    TableRepository::class);
        $this->app->bind(PromoRepositoryInterface::class,    PromoRepository::class);
    }
}