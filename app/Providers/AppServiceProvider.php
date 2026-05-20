<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Ticket;
use App\Policies\CommentPolicy;
use App\Policies\TicketPolicy;
use App\Services\NotificationService;
use App\Services\TicketService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services as singletons
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(TicketService::class, function ($app) {
            return new TicketService($app->make(NotificationService::class));
        });
    }

    public function boot(): void
    {
        // Strict mode in development — catch lazy loading, missing attributes, etc.
        Model::shouldBeStrict(!app()->isProduction());

        // Register policies
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
    }
}