<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ActivityFeed;
use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckExpiringContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-expiring-contracts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek kontrak yang akan segera berakhir dan kirim pengingat, serta tandai yang sudah expired.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->handleExpiredContracts();
        $this->handleReminders();

        $this->info('Contract lifecycle check completed.');
    }

    protected function handleExpiredContracts()
    {
        $expiredCount = Contract::query()
            ->where('status', '!=', 'expired')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->startOfDay())
            ->each(function (Contract $contract) {
                $oldStatus = $contract->status;
                $contract->update(['status' => 'expired']);

                ActivityFeed::query()->create([
                    'workspace_id' => $contract->workspace_id,
                    'user_id' => $contract->created_by,
                    'type' => 'contract',
                    'subject_type' => Contract::class,
                    'subject_id' => $contract->id,
                    'description' => sprintf('Kontrak %s telah otomatis ditandai sebagai EXPIRED.', $contract->title),
                    'metadata' => [
                        'action' => 'status_update',
                        'old_status' => $oldStatus,
                        'new_status' => 'expired',
                        'icon' => 'ClockAlert',
                        'color' => 'rose',
                    ],
                ]);
            });

        if ($expiredCount) {
            $this->info("Processed expired contracts.");
        }
    }

    protected function handleReminders()
    {
        // Ambil kontrak yang belum signed atau yang aktif tapi mendekati end_date
        Contract::query()
            ->whereIn('status', ['sent', 'signed'])
            ->whereNotNull('end_date')
            ->where('end_date', '>', now())
            ->each(function (Contract $contract) {
                $daysRemaining = now()->diffInDays($contract->end_date, false);
                
                // Jika tepat di hari reminder
                if ($daysRemaining == $contract->reminder_days_before) {
                    $this->sendReminder($contract, $daysRemaining);
                }
            });
    }

    protected function sendReminder(Contract $contract, $daysRemaining)
    {
        // 1. Catat ke Activity Feed
        ActivityFeed::query()->create([
            'workspace_id' => $contract->workspace_id,
            'user_id' => $contract->created_by,
            'type' => 'contract',
            'subject_type' => Contract::class,
            'subject_id' => $contract->id,
            'description' => sprintf('PENGINGAT: Kontrak %s akan berakhir dalam %d hari.', $contract->title, $daysRemaining),
            'metadata' => [
                'action' => 'reminder',
                'icon' => 'BellRing',
                'color' => 'amber',
            ],
        ]);

        // 2. Placeholder untuk Email/WA
        // Di sini bisa ditambahkan logic notifikasi ke Client atau Owner Workspace
        $this->line("Reminder sent for contract: " . $contract->title);
    }
}
