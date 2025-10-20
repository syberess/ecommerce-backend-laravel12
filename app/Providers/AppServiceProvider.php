<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Interfaces\ICategoryRepository;
use App\Infrastructure\Repositories\CategoryRepository;
use App\Core\Interfaces\IProductRepository;
use App\Infrastructure\Repositories\ProductRepository;
use App\Core\Interfaces\IPaymentRepository;
use App\Infrastructure\Repositories\PaymentRepository;
use App\Core\Interfaces\IReportRepository;
use App\Infrastructure\Repositories\ReportRepository;
use App\Core\Interfaces\ICartRepository;
use App\Infrastructure\Repositories\CartRepository;
use Illuminate\Support\Facades\Event;
use App\Events\OrderCreated;
use App\Listeners\SendOrderNotification;
use App\Core\Interfaces\IBaseRepository;
use App\Infrastructure\Repositories\BaseRepository;

use Illuminate\Support\Facades\Gate;
use App\Core\Entities\Cart;
use App\Http\Policies\CartPolicy; 

use App\Events\PaymentCompleted;
use App\Listeners\UpdateOrderStatusOnPayment;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    
    public function register(): void
    {
        $this->app->bind(ICategoryRepository::class, CategoryRepository::class);
        $this->app->bind(IProductRepository::class, ProductRepository::class);
          $this->app->bind(
        \App\Core\Interfaces\IOrderRepository::class,
        \App\Infrastructure\Repositories\OrderRepository::class
    );
       $this->app->bind(IPaymentRepository::class, PaymentRepository::class);
        
       $this->app->bind(IReportRepository::class, ReportRepository::class);
       $this->app->bind(ICartRepository::class, CartRepository::class);
       $this->app->bind(IBaseRepository::class, BaseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
            Event::listen(
            OrderCreated::class,
            [SendOrderNotification::class, 'handle']
        );
        Event::listen(PaymentCompleted::class, [UpdateOrderStatusOnPayment::class, 'handle']);
        Gate::policy(Cart::class, CartPolicy::class);

    }
}
