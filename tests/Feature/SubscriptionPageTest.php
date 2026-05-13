<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SubscriptionPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
    }

    public function test_subscription_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->subscriptionContext();
        $vendor = $this->createVendor($workspace, 'Figma');
        $account = $this->createAccount($workspace, 'BCA Tools');
        $expense = $this->createExpenseTransaction($workspace, $owner, $account, 'design_tools', 240000);

        Subscription::query()->create([
            'workspace_id' => $workspace->getKey(),
            'vendor_id' => $vendor->getKey(),
            'transaction_id' => $expense->getKey(),
            'name' => 'Figma Professional',
            'description' => 'Design collaboration tool',
            'amount' => 240000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'next_renewal_date' => now()->addDays(3)->toDateString(),
            'reminder_days_before' => 5,
        ]);

        Subscription::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Cloudflare Pro',
            'amount' => 1200000,
            'billing_cycle' => 'yearly',
            'status' => 'active',
            'next_renewal_date' => now()->subDays(1)->toDateString(),
            'reminder_days_before' => 10,
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.finance.subscriptions.index', [
                'workspace' => $workspace,
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Finance/Subscriptions/Index')
                ->where('workspace.slug', 'velora')
                ->where('subscriptions.summary.total_subscriptions', 2)
                ->where('subscriptions.summary.due_soon_subscriptions', 1)
                ->where('subscriptions.summary.expired_subscriptions', 1)
                ->where('subscriptions.items.0.name', 'Cloudflare Pro')
                ->where('subscriptions.items.0.effective_status', 'expired')
                ->where('subscriptions.items.1.vendor.name', 'Figma')
                ->where('vendors.summary.total_vendors', 1)
                ->has('vendors.items', 1)
                ->has('filterOptions.vendors', 1)
                ->has('filterOptions.expenseTransactions', 1)
            );
    }

    public function test_subscription_and_vendor_can_be_created_updated_status_changed_and_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->subscriptionContext();
        $account = $this->createAccount($workspace, 'Mandiri SaaS');
        $expense = $this->createExpenseTransaction($workspace, $owner, $account, 'tools', 450000);

        $vendorCreate = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.subscriptions.vendors.store', $workspace), [
                'name' => 'Notion',
                'contact_name' => 'Sales Team',
                'email' => 'sales@notion.test',
                'phone' => '08123456789',
                'contract' => 'Annual vendor contract',
                'payment_terms' => 'Annual billing upfront',
                'notes' => 'Workspace knowledge base',
            ]);

        $vendorCreate->assertRedirect();

        $vendor = Vendor::query()->where('name', 'Notion')->firstOrFail();

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->getKey(),
            'workspace_id' => $workspace->getKey(),
            'email' => 'sales@notion.test',
            'contract' => 'Annual vendor contract',
        ]);

        $subscriptionCreate = $this
            ->actingAs($owner)
            ->post(route('workspace.finance.subscriptions.store', $workspace), [
                'vendor_id' => $vendor->getKey(),
                'transaction_id' => $expense->getKey(),
                'name' => 'Notion Plus',
                'description' => 'Internal docs workspace',
                'amount' => 450000,
                'billing_cycle' => 'monthly',
                'status' => 'active',
                'next_renewal_date' => now()->addDays(30)->toDateString(),
                'reminder_days_before' => 7,
            ]);

        $subscriptionCreate->assertRedirect();

        $subscription = Subscription::query()->where('name', 'Notion Plus')->firstOrFail();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->getKey(),
            'workspace_id' => $workspace->getKey(),
            'vendor_id' => $vendor->getKey(),
            'transaction_id' => $expense->getKey(),
            'billing_cycle' => 'monthly',
        ]);

        $subscriptionUpdate = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.subscriptions.update', [
                'workspace' => $workspace,
                'subscription' => $subscription,
            ]), [
                'vendor_id' => $vendor->getKey(),
                'transaction_id' => $expense->getKey(),
                'name' => 'Notion Business',
                'description' => 'Updated plan',
                'amount' => 600000,
                'billing_cycle' => 'yearly',
                'status' => 'active',
                'next_renewal_date' => now()->addDays(365)->toDateString(),
                'reminder_days_before' => 14,
            ]);

        $subscriptionUpdate->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->getKey(),
            'name' => 'Notion Business',
            'amount' => 600000,
            'billing_cycle' => 'yearly',
            'reminder_days_before' => 14,
        ]);

        $statusUpdate = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.subscriptions.status.update', [
                'workspace' => $workspace,
                'subscription' => $subscription,
            ]), [
                'status' => 'cancelled',
            ]);

        $statusUpdate->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->getKey(),
            'status' => 'cancelled',
        ]);

        $vendorUpdate = $this
            ->actingAs($owner)
            ->patch(route('workspace.finance.subscriptions.vendors.update', [
                'workspace' => $workspace,
                'vendor' => $vendor,
            ]), [
                'name' => 'Notion HQ',
                'contact_name' => 'Success Team',
                'email' => 'success@notion.test',
                'phone' => '0899999999',
                'contract' => 'Updated contract notes',
                'payment_terms' => 'Updated terms',
                'notes' => 'Updated notes',
            ]);

        $vendorUpdate->assertRedirect();

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->getKey(),
            'name' => 'Notion HQ',
            'email' => 'success@notion.test',
            'contract' => 'Updated contract notes',
        ]);

        $subscriptionDelete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.subscriptions.destroy', [
                'workspace' => $workspace,
                'subscription' => $subscription,
            ]));

        $subscriptionDelete->assertRedirect();

        $this->assertDatabaseMissing('subscriptions', [
            'id' => $subscription->getKey(),
        ]);

        $vendorDelete = $this
            ->actingAs($owner)
            ->delete(route('workspace.finance.subscriptions.vendors.destroy', [
                'workspace' => $workspace,
                'vendor' => $vendor,
            ]));

        $vendorDelete->assertRedirect();

        $this->assertDatabaseMissing('vendors', [
            'id' => $vendor->getKey(),
        ]);
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    protected function subscriptionContext(): array
    {
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();
        $owner = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();

        return [$owner, $workspace];
    }

    protected function createVendor(Workspace $workspace, string $name): Vendor
    {
        return Vendor::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'contact_name' => 'Vendor PIC',
            'email' => strtolower($name) . '@vendor.test',
            'phone' => '0812000000',
            'contract' => 'Vendor contract note',
            'payment_terms' => 'Monthly auto debit',
            'notes' => 'Vendor note',
        ]);
    }

    protected function createAccount(Workspace $workspace, string $name): BankAccount
    {
        return BankAccount::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'type' => 'bank',
            'balance' => 0,
            'is_active' => true,
        ]);
    }

    protected function createExpenseTransaction(Workspace $workspace, User $owner, BankAccount $account, string $category, float $amount): Transaction
    {
        return Transaction::query()->create([
            'workspace_id' => $workspace->getKey(),
            'account_id' => $account->getKey(),
            'type' => 'expense',
            'category' => $category,
            'amount' => $amount,
            'description' => 'Expense for subscription',
            'date' => now()->toDateString(),
            'created_by' => $owner->getKey(),
            'created_at' => now(),
        ]);
    }
}
