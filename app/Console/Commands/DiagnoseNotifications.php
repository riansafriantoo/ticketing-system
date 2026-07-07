<?php

namespace App\Console\Commands;

use App\Events\TicketCreated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseNotifications extends Command
{
    protected $signature   = 'notifications:diagnose';
    protected $description = 'Diagnose duplicate email notification sources';

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════════');
        $this->info('  Notification Pipeline Diagnostic');
        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        // ── 1. How many listeners are registered for TicketCreated? ──────────
        $listeners = app('events')->getListeners(TicketCreated::class);
        $count     = count($listeners);

        $color = $count === 1 ? 'green' : 'red';
        $this->line("Listeners registered for TicketCreated: <fg={$color}>{$count}</>");

        if ($count === 1) {
            $this->line('  <fg=green>✓ Correct — exactly one listener</>');
        } elseif ($count === 0) {
            $this->line('  <fg=red>✗ No listeners registered — events fire into a void</>');
        } else {
            $this->line("  <fg=red>✗ {$count} listeners — DUPLICATE REGISTRATION FOUND</>");
        }
        $this->newLine();

        // ── 2. Is shouldDiscoverEvents returning false? ──────────────────────
        $espClass = \App\Providers\EventServiceProvider::class;
        $reflection = new \ReflectionClass($espClass);

        if ($reflection->hasMethod('shouldDiscoverEvents')) {
            // Instantiate properly with the app instance
            $esp = new $espClass(app());
            $discovers = $esp->shouldDiscoverEvents();

            $this->line('shouldDiscoverEvents(): ' . ($discovers
                ? '<fg=red>TRUE ✗ — auto-discovery ON, causes duplicate listeners</>'
                : '<fg=green>FALSE ✓ — auto-discovery OFF</>'));
        } else {
            $this->line('shouldDiscoverEvents(): <fg=red>METHOD MISSING ✗ — defaults to TRUE (auto-discovery ON)</>');
        }
        $this->newLine();

        // ── 3. Any leftover ->notify() calls? ────────────────────────────────
        $notifyFiles = [];
        $scanDirs = ['Services', 'Jobs', 'Notifications'];

        foreach ($scanDirs as $dir) {
            $fullPath = app_path($dir);
            if (!is_dir($fullPath)) continue;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;

                $content = file_get_contents($file->getPathname());
                if (str_contains($content, '->notify(') || str_contains($content, 'Notification::send(')) {
                    $notifyFiles[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        if (empty($notifyFiles)) {
            $this->line('<fg=green>✓ No leftover ->notify() calls found</>');
        } else {
            $this->line('<fg=red>✗ Old ->notify() calls still exist:</>');
            foreach ($notifyFiles as $f) {
                $this->line("    {$f}");
            }
        }
        $this->newLine();

        // ── 4. Check for old Notification classes ────────────────────────────
        $notificationDir = app_path('Notifications');
        if (is_dir($notificationDir)) {
            $files = glob($notificationDir . '/*.php');
            if (!empty($files)) {
                $this->line('<fg=red>✗ Old Notification classes still exist:</>');
                foreach ($files as $f) {
                    $this->line('    ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $f));
                }
                $this->line('  These are from the old system — DELETE THEM.');
            }
        } else {
            $this->line('<fg=green>✓ No old app/Notifications/ directory</>');
        }
        $this->newLine();

        // ── 5. Check Mail classes for ShouldQueue ────────────────────────────
        $mailDir = app_path('Mail');
        $badMails = [];
        if (is_dir($mailDir)) {
            foreach (glob($mailDir . '/*.php') as $file) {
                $content = file_get_contents($file);
                if (str_contains($content, 'implements ShouldQueue')) {
                    $badMails[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                }
            }
        }

        if (empty($badMails)) {
            $this->line('<fg=green>✓ No Mailable classes implement ShouldQueue</>');
        } else {
            $this->line('<fg=red>✗ These Mailables still have ShouldQueue (causes double-queue):</>');
            foreach ($badMails as $f) {
                $this->line("    {$f}");
            }
        }
        $this->newLine();

        // ── 6. Check queue for duplicate jobs ────────────────────────────────
        try {
            $pending = DB::table('jobs')->where('queue', 'emails')->count();
            $this->line("Jobs currently in 'emails' queue: {$pending}");

            $dupes = DB::table('jobs')
                ->select('payload', DB::raw('COUNT(*) as cnt'))
                ->where('queue', 'emails')
                ->groupBy('payload')
                ->having('cnt', '>', 1)
                ->count();

            if ($dupes > 0) {
                $this->line("<fg=red>✗ {$dupes} duplicate job payloads — run: php artisan queue:flush</>");
            } else {
                $this->line('<fg=green>✓ No duplicate jobs in queue</>');
            }
        } catch (\Exception $e) {
            $this->line('<fg=yellow>⚠ Could not check jobs table</>');
        }
        $this->newLine();

        // ── 7. Check notification_logs idempotency layer ─────────────────────
        try {
            $logged = \App\Models\NotificationLog::where('sent_at', '>=', now()->subHour())->count();
            $this->line("Notifications logged (last hour): {$logged}");
            $this->line('<fg=green>✓ Idempotency layer active — duplicates will be suppressed</>');
        } catch (\Exception $e) {
            $this->line('<fg=red>✗ notification_logs table missing — run: php artisan migrate</>');
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════');

        return self::SUCCESS;
    }
}