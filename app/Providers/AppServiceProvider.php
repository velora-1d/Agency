<?php

namespace App\Providers;

use App\Events\InvoicePaid;
use App\Events\LeadCreated;
use App\Events\SupportTicketCreated;
use App\Listeners\TriggerN8nAutomation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {}

    public function boot(): void
    {
        Event::listen(LeadCreated::class, [TriggerN8nAutomation::class, 'handle']);
        Event::listen(InvoicePaid::class, [TriggerN8nAutomation::class, 'handle']);
        Event::listen(SupportTicketCreated::class, [TriggerN8nAutomation::class, 'handle']);
    }
}
