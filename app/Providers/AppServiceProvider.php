<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Asset;
use App\Models\Ticket;
use App\Policies\CommentPolicy;
use App\Policies\AssetPolicy;
use App\Services\AssetService;
use App\Policies\TicketPolicy;
use App\Services\TicketService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        // Strict mode in development — catch lazy loading, missing attributes, etc.
        Model::shouldBeStrict(!app()->isProduction());

        // Register policies
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Asset::class, AssetPolicy::class);
    }
}