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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict(!app()->isProduction());

        // Event::listen(TicketCreated::class, SendTicketCreatedEmail::class);
        // Event::listen(TicketStatusChanged::class, SendTicketStatusChangedEmail::class);
        // Event::listen(TicketAssigned::class, SendTicketAssignedEmail::class);
        // Event::listen(CommentAdded::class, SendCommentAddedEmail::class);
    }
}