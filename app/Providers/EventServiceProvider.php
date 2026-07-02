<?php

namespace App\Providers;

use App\Events\CommentAdded;
use App\Events\TicketAssigned;
use App\Events\TicketCreated;
use App\Events\TicketStatusChanged;
use App\Listeners\SendCommentAddedEmail;
use App\Listeners\SendTicketAssignedEmail;
use App\Listeners\SendTicketCreatedEmail;
use App\Listeners\SendTicketStatusChangedEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Explicit event → listener map for the ticket notification feature.
 *
 * Laravel 11 defaults to auto-discovering listeners, but for a feature
 * this central to the product (every user-facing notification flows
 * through here), an explicit map is worth the few extra lines — anyone
 * reading this file sees the entire notification surface area in one
 * place, with no need to grep listener classes to find what's wired up.
 *
 * Register this provider in bootstrap/providers.php:
 *   App\Providers\EventServiceProvider::class,
 */
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TicketCreated::class => [
            SendTicketCreatedEmail::class,
        ],

        TicketStatusChanged::class => [
            SendTicketStatusChangedEmail::class,
        ],

        TicketAssigned::class => [
            SendTicketAssignedEmail::class,
        ],

        CommentAdded::class => [
            SendCommentAddedEmail::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}