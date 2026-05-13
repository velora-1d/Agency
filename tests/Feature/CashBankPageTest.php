<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class CashBankPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_cash_bank_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->context();
        $bank = $this->createAccount($workspace, 'BCA Operasional', 'bank');
        $cash = $this->createAccount($workspace, 'Kas Kecil', 'cash');

        Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $bank->getKey(),
            'type' => 'income',
            'category' => 'opening_balance',
            'amount' => 5000000,
            'description' => 'Opening',
            'date' => now()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $cash->getKey(),
            'type' => 'income',
            'category' => 'opening_balance',
            'amount' => 750000,
            'description' => 'Opening',
            'date' => now()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        $bank->refresh();
        $cash->refresh();

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.cash-bank.index', $workspace));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/CashBank/Index')
                ->where('workspace.slug', 'velora')
                ->where('accounts.summary.total_accounts', 2)
                ->where('accounts.summary.active_accounts', 2)
                ->where('accounts.items.0.name', 'BCA Operasional')
                ->has('accounts.items.0.transactions', 1)
                ->has('filterOptions.types', 3)
                ->has('filterOptions.accounts', 2)
            );
    }

    public function test_account_can_be_created_updated_transferred_reconciled_and_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->context();

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.cash-bank.store', $workspace), [
                'name' => 'Mandiri Project',
                'bank_name' => 'Mandiri',
                'account_number' => '1234567890',
                'account_holder' => 'Velora',
                'type' => 'bank',
                'opening_balance' => 3000000,
                'is_active' => true,
            ]);

        $create->assertRedirect();

        $source = BankAccount::query()->where('name', 'Mandiri Project')->firstOrFail();
        $target = $this->createAccount($workspace, 'Kas Marketing', 'cash');

        $source->refresh();
        $target->refresh();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $source->getKey(),
            'bank_name' => 'Mandiri',
        ]);

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.cash-bank.update', [
                'workspace' => $workspace,
                'account' => $source,
            ]), [
                'name' => 'Mandiri Main',
                'bank_name' => 'Mandiri',
                'account_number' => '1234567890',
                'account_holder' => 'Velora HQ',
                'type' => 'bank',
                'is_active' => true,
            ]);

        $update->assertRedirect();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $source->getKey(),
            'name' => 'Mandiri Main',
        ]);

        $transfer = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.cash-bank.transfer', $workspace), [
                'from_account_id' => $source->getKey(),
                'to_account_id' => $target->getKey(),
                'amount' => 500000,
                'date' => now()->toDateString(),
                'description' => 'Top up petty cash',
            ]);

        $transfer->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'account_id' => $source->getKey(),
            'category' => 'transfer_out',
            'amount' => 500000,
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $target->getKey(),
            'category' => 'transfer_in',
            'amount' => 500000,
        ]);

        $reconcile = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.cash-bank.reconcile', [
                'workspace' => $workspace,
                'account' => $source,
            ]), [
                'last_reconciled_at' => now()->toDateString(),
                'reconciliation_notes' => 'Statement matched.',
            ]);

        $reconcile->assertRedirect();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $source->getKey(),
            'reconciliation_notes' => 'Statement matched.',
        ]);

        $delete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.cash-bank.destroy', [
                'workspace' => $workspace,
                'account' => $target,
            ]));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('bank_accounts', [
            'id' => $target->getKey(),
        ]);
    }

    protected function context(): array
    {
        return [
            User::query()->where('email', 'owner@kantordigital.test')->firstOrFail(),
            Workspace::query()->where('slug', 'velora')->firstOrFail(),
        ];
    }

    protected function createAccount(Workspace $workspace, string $name, string $type): BankAccount
    {
        return BankAccount::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'type' => $type,
            'balance' => 0,
            'is_active' => true,
        ]);
    }
}
