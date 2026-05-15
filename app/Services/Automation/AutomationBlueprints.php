<?php

namespace App\Services\Automation;

class AutomationBlueprints
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function triggerEvents(): array
    {
        return [
            ['value' => 'lead_created', 'label' => 'Prospek Baru'],
            ['value' => 'invoice_due', 'label' => 'Jatuh Tempo Tagihan'],
            ['value' => 'task_completed', 'label' => 'Tugas Selesai'],
            ['value' => 'payment_received', 'label' => 'Pembayaran Diterima'],
            ['value' => 'form_submitted', 'label' => 'Formulir Masuk'],
            ['value' => 'support_ticket_created', 'label' => 'Tiket Dukungan Baru'],
            ['value' => 'schedule', 'label' => 'Pemicu Terjadwal'],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function triggerTypes(): array
    {
        return [
            ['value' => 'event', 'label' => 'Kejadian (Event)'],
            ['value' => 'schedule', 'label' => 'Jadwal (Schedule)'],
            ['value' => 'webhook', 'label' => 'Picu Webhook'],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function stepTypes(): array
    {
        return [
            ['value' => 'send_whatsapp', 'label' => 'Kirim WhatsApp'],
            ['value' => 'send_email', 'label' => 'Kirim Email'],
            ['value' => 'create_task', 'label' => 'Buat Tugas'],
            ['value' => 'update_status', 'label' => 'Perbarui Status'],
            ['value' => 'notify_team', 'label' => 'Notifikasi Tim'],
            ['value' => 'webhook', 'label' => 'Panggil Webhook (n8n)'],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function conditionOperators(): array
    {
        return [
            ['value' => 'equals', 'label' => 'Sama Dengan'],
            ['value' => 'not_equals', 'label' => 'Tidak Sama Dengan'],
            ['value' => 'contains', 'label' => 'Mengandung'],
            ['value' => 'exists', 'label' => 'Ada / Terisi'],
            ['value' => 'greater_than', 'label' => 'Lebih Dari'],
            ['value' => 'less_than', 'label' => 'Kurang Dari'],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function statusOptions(): array
    {
        return [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function templates(): array
    {
        return [
            [
                'key' => 'lead_follow_up',
                'name' => 'Lead Follow Up',
                'description' => 'Auto WA + notify team saat lead baru masuk.',
                'trigger_event' => 'lead_created',
                'trigger_type' => 'event',
                'retry_enabled' => true,
                'retry_limit' => 3,
                'conditions' => [
                    ['field' => 'lead.priority', 'operator' => 'equals', 'value' => 'high'],
                ],
                'steps' => [
                    ['type' => 'send_whatsapp', 'label' => 'Kirim WhatsApp follow up', 'target' => 'lead.phone', 'message' => 'Halo {lead.name}, kami sudah menerima inquiry Anda.'],
                    ['type' => 'notify_team', 'label' => 'Notifikasi sales', 'target' => 'sales_team', 'message' => 'Lead baru butuh respon cepat.'],
                ],
            ],
            [
                'key' => 'invoice_reminder',
                'name' => 'Invoice Reminder',
                'description' => 'Reminder otomatis untuk invoice yang mendekati due date.',
                'trigger_event' => 'invoice_due',
                'trigger_type' => 'event',
                'retry_enabled' => true,
                'retry_limit' => 2,
                'conditions' => [
                    ['field' => 'invoice.status', 'operator' => 'not_equals', 'value' => 'paid'],
                ],
                'steps' => [
                    ['type' => 'send_email', 'label' => 'Kirim email invoice', 'target' => 'client.email', 'message' => 'Invoice Anda akan jatuh tempo hari ini.'],
                    ['type' => 'send_whatsapp', 'label' => 'Kirim WA reminder', 'target' => 'client.phone', 'message' => 'Reminder: invoice Anda segera jatuh tempo.'],
                ],
            ],
            [
                'key' => 'task_complete_notify',
                'name' => 'Task Complete Notify',
                'description' => 'Notifikasi tim ketika task selesai.',
                'trigger_event' => 'task_completed',
                'trigger_type' => 'event',
                'retry_enabled' => false,
                'retry_limit' => 0,
                'conditions' => [],
                'steps' => [
                    ['type' => 'notify_team', 'label' => 'Umumkan task selesai', 'target' => 'project_team', 'message' => 'Task selesai dan siap dicek.'],
                    ['type' => 'create_task', 'label' => 'Buat follow-up task', 'target' => 'project_backlog', 'message' => 'Buat task tindak lanjut bila dibutuhkan.'],
                ],
            ],
            [
                'key' => 'payment_sync',
                'name' => 'Payment Sync',
                'description' => 'Sinkron payment masuk ke finance dan client update.',
                'trigger_event' => 'payment_received',
                'trigger_type' => 'event',
                'retry_enabled' => true,
                'retry_limit' => 3,
                'conditions' => [
                    ['field' => 'payment.amount', 'operator' => 'greater_than', 'value' => '0'],
                ],
                'steps' => [
                    ['type' => 'update_status', 'label' => 'Update status transaksi', 'target' => 'invoice', 'message' => 'Tandai invoice sebagai paid.'],
                    ['type' => 'send_email', 'label' => 'Kirim receipt', 'target' => 'client.email', 'message' => 'Terima kasih, payment sudah diterima.'],
                ],
            ],
            [
                'key' => 'form_router',
                'name' => 'Form Router',
                'description' => 'Routing submission ke task dan notifikasi workspace.',
                'trigger_event' => 'form_submitted',
                'trigger_type' => 'event',
                'retry_enabled' => false,
                'retry_limit' => 0,
                'conditions' => [],
                'steps' => [
                    ['type' => 'create_task', 'label' => 'Buat task intake', 'target' => 'operations', 'message' => 'Buat task follow up dari form submission.'],
                    ['type' => 'notify_team', 'label' => 'Notifikasi tim', 'target' => 'ops_team', 'message' => 'Submission baru masuk ke workspace.'],
                ],
            ],
        ];
    }

    public function template(?string $key): ?array
    {
        if (! filled($key)) {
            return null;
        }

        return collect($this->templates())->firstWhere('key', $key);
    }

    /**
     * @return array<string, mixed>
     */
    public function blankStep(): array
    {
        return [
            'type' => 'send_whatsapp',
            'label' => '',
            'target' => '',
            'message' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function blankCondition(): array
    {
        return [
            'field' => '',
            'operator' => 'equals',
            'value' => '',
        ];
    }
}
