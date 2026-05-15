<?php

use App\Http\Controllers\App\Communication\ActivityFeedController;
use App\Http\Controllers\App\Communication\CalendarController;
use App\Http\Controllers\App\Communication\InboxController;
use App\Http\Controllers\App\Communication\NotificationsController;
use App\Http\Controllers\App\Automation\AutomationController;
use App\Http\Controllers\App\CRM\ClientController;
use App\Http\Controllers\App\CRM\LeadController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\DigitalServices\DigitalServiceController;
use App\Http\Controllers\App\Finance\FinanceOverviewController;
use App\Http\Controllers\App\Finance\BillingController;
use App\Http\Controllers\App\Finance\CashBankController;
use App\Http\Controllers\App\Finance\ExpenseController;
use App\Http\Controllers\App\Finance\FinancialReportController;
use App\Http\Controllers\App\Finance\InvoiceController;
use App\Http\Controllers\App\Marketing\MarketingController;
use App\Http\Controllers\App\Marketing\CampaignController;
use App\Http\Controllers\App\Marketing\SocialPostController;
use App\Http\Controllers\App\Marketing\NewsletterController;
use App\Http\Controllers\App\Finance\PayrollController;
use App\Http\Controllers\App\Finance\QuotationController;
use App\Http\Controllers\App\Finance\QuotationPublicController;
use App\Http\Controllers\App\Finance\SubscriptionController;
use App\Http\Controllers\App\Finance\TransactionController;
use App\Http\Controllers\App\Project\ProjectController;
use App\Http\Controllers\App\Project\ContractController;
use App\Http\Controllers\App\Project\ContractTemplateController;
use App\Http\Controllers\App\Project\FileController;
use App\Http\Controllers\App\Project\MeetingController;
use App\Http\Controllers\App\Project\NoteController;
use App\Http\Controllers\App\Project\ProjectTemplateController;
use App\Http\Controllers\App\Project\TaskController;
use App\Http\Controllers\App\System\SystemController;
use App\Http\Controllers\App\System\SecurityVerificationController;
use App\Http\Controllers\App\System\ExecutiveHubController;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/app', function (): RedirectResponse {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $workspace = $user?->firstAccessibleWorkspace();

        abort_if($workspace === null, 403, 'No workspace assigned.');

        return redirect()->route('workspace.dashboard', $workspace);
    })->name('app.home');

    // Security Verification (Global)
    Route::get('/app/verification/check', [SecurityVerificationController::class, 'check'])->name('app.verification.check');
    Route::post('/app/verification/verify', [SecurityVerificationController::class, 'verify'])->name('app.verification.verify');
    Route::post('/app/verification/pin', [SecurityVerificationController::class, 'updatePin'])->name('app.verification.pin.update');

    Route::prefix('w/{workspace:slug}')
        ->middleware(['setWorkspace', 'ensureWorkspaceAccess'])
        ->name('workspace.')
        ->group(function (): void {
            Route::get('/', function (Workspace $workspace) {
                return redirect()->route('workspace.dashboard', $workspace);
            });
            Route::get('/dashboard', DashboardController::class)->name('dashboard');

            Route::prefix('crm')->name('crm.')->group(function (): void {
                Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
                Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
                Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
                Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
                Route::patch('/leads/{lead}/stage', [LeadController::class, 'moveStage'])->name('leads.move-stage');
                Route::patch('/leads/{lead}/notes', [LeadController::class, 'updateNotes'])->name('leads.notes.update');
                Route::post('/leads/{lead}/activities', [LeadController::class, 'storeActivity'])->name('leads.activities.store');
                Route::post('/leads/{lead}/convert', [LeadController::class, 'convertToClient'])->name('leads.convert');
                Route::patch('/leads/automation/settings', [LeadController::class, 'updateAutomation'])->name('leads.automation.update');
                Route::post('/leads/pipelines', [LeadController::class, 'storePipeline'])->name('leads.pipelines.store');
                Route::patch('/leads/pipelines/{pipeline}', [LeadController::class, 'updatePipeline'])->name('leads.pipelines.update');
                Route::delete('/leads/pipelines/{pipeline}', [LeadController::class, 'destroyPipeline'])->name('leads.pipelines.destroy');
                Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
                Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
                Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
                Route::patch('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
                Route::patch('/clients/{client}/notes', [ClientController::class, 'updateNotes'])->name('clients.notes.update');
                Route::post('/clients/{client}/activities', [ClientController::class, 'storeActivity'])->name('clients.activities.store');
                Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
                Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
            });

            Route::prefix('projects')->name('projects.')->group(function (): void {
                Route::get('/', [ProjectController::class, 'index'])->name('index');
                Route::post('/', [ProjectController::class, 'store'])->name('store');
                Route::patch('/{project}/status', [ProjectController::class, 'updateStatus'])->name('status.update');
                Route::patch('/{project}', [ProjectController::class, 'update'])->name('update');
                Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');

                Route::prefix('templates')->name('templates.')->group(function (): void {
                    Route::post('/', [ProjectTemplateController::class, 'store'])->name('store');
                    Route::patch('/{template}', [ProjectTemplateController::class, 'update'])->name('update');
                    Route::delete('/{template}', [ProjectTemplateController::class, 'destroy'])->name('destroy');
                });

                Route::prefix('contracts')->name('contracts.')->group(function (): void {
                    Route::get('/', [ContractController::class, 'index'])->name('index');
                    Route::post('/', [ContractController::class, 'store'])->name('store');
                    Route::get('/templates', [ContractTemplateController::class, 'index'])->name('templates.index');
                    Route::post('/templates', [ContractTemplateController::class, 'store'])->name('templates.store');
                    Route::patch('/templates/{template}', [ContractTemplateController::class, 'update'])->name('templates.update');
                    Route::delete('/templates/{template}', [ContractTemplateController::class, 'destroy'])->name('templates.destroy');
                    Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
                    Route::patch('/{contract}', [ContractController::class, 'update'])->name('update');
                    Route::patch('/{contract}/status', [ContractController::class, 'updateStatus'])->name('status.update');
                    Route::post('/{contract}/upload-signed', [ContractController::class, 'uploadSigned'])->name('upload-signed');
                    Route::post('/{contract}/send-wa', [ContractController::class, 'sendWhatsApp'])->name('send-wa');
                    Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');
                });

                Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
            });

            Route::prefix('tasks')->name('tasks.')->group(function (): void {
                Route::get('/', [TaskController::class, 'index'])->name('index');
                Route::post('/', [TaskController::class, 'store'])->name('store');
                Route::patch('/{task}', [TaskController::class, 'update'])->name('update');
                Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('status.update');
                Route::post('/{task}/time-logs', [TaskController::class, 'storeTimeLog'])->name('time-logs.store');
                Route::post('/{task}/comments', [TaskController::class, 'storeComment'])->name('comments.store');
                Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');

                Route::prefix('templates')->name('templates.')->group(function (): void {
                    Route::post('/', [TaskController::class, 'storeTemplate'])->name('store');
                    Route::patch('/{template}', [TaskController::class, 'updateTemplate'])->name('update');
                    Route::delete('/{template}', [TaskController::class, 'destroyTemplate'])->name('destroy');
                });
            });

            Route::prefix('meetings')->name('meetings.')->group(function (): void {
                Route::get('/', [MeetingController::class, 'index'])->name('index');
                Route::post('/', [MeetingController::class, 'store'])->name('store');
                Route::patch('/{meeting}', [MeetingController::class, 'update'])->name('update');
                Route::delete('/{meeting}', [MeetingController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('notes')->name('notes.')->group(function (): void {
                Route::get('/', [NoteController::class, 'index'])->name('index');
                Route::post('/', [NoteController::class, 'store'])->name('store');
                Route::patch('/{note}', [NoteController::class, 'update'])->name('update');
                Route::delete('/{note}', [NoteController::class, 'destroy'])->name('destroy');

                Route::prefix('folders')->name('folders.')->group(function (): void {
                    Route::post('/', [NoteController::class, 'storeFolder'])->name('store');
                    Route::patch('/{folder}', [NoteController::class, 'updateFolder'])->name('update');
                    Route::delete('/{folder}', [NoteController::class, 'destroyFolder'])->name('destroy');
                });
            });

            Route::prefix('files')->name('files.')->group(function (): void {
                Route::get('/', [FileController::class, 'index'])->name('index');
                Route::post('/', [FileController::class, 'store'])->name('store');
                Route::patch('/{file}', [FileController::class, 'update'])->name('update');
                Route::delete('/{file}', [FileController::class, 'destroy'])->name('destroy');
                Route::patch('/{file}/approval', [FileController::class, 'updateApproval'])->name('approval.update');
                Route::patch('/{file}/share', [FileController::class, 'updateShare'])->name('share.update');

                Route::prefix('folders')->name('folders.')->group(function (): void {
                    Route::post('/', [FileController::class, 'storeFolder'])->name('store');
                    Route::patch('/{folder}', [FileController::class, 'updateFolder'])->name('update');
                    Route::delete('/{folder}', [FileController::class, 'destroyFolder'])->name('destroy');
                });
            });

            Route::prefix('finance')->name('finance.')->group(function (): void {
                Route::get('/', [FinanceOverviewController::class, 'index'])->name('overview');
                Route::prefix('subscriptions')->name('subscriptions.')->group(function (): void {
                    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
                    Route::post('/', [SubscriptionController::class, 'store'])->name('store');
                    Route::patch('/{subscription}', [SubscriptionController::class, 'update'])->name('update');
                    Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('destroy');
                    Route::patch('/{subscription}/status', [SubscriptionController::class, 'updateStatus'])->name('status.update');
                    Route::post('/vendors', [SubscriptionController::class, 'storeVendor'])->name('vendors.store');
                    Route::patch('/vendors/{vendor}', [SubscriptionController::class, 'updateVendor'])->name('vendors.update');
                    Route::delete('/vendors/{vendor}', [SubscriptionController::class, 'destroyVendor'])->name('vendors.destroy');
                });
                Route::prefix('billings')->name('billings.')->group(function (): void {
                    Route::get('/', [BillingController::class, 'index'])->name('index');
                    Route::post('/', [BillingController::class, 'store'])->name('store');
                    Route::patch('/{billing}', [BillingController::class, 'update'])->name('update');
                    Route::delete('/{billing}', [BillingController::class, 'destroy'])->name('destroy');
                    Route::post('/generate-due', [BillingController::class, 'generateDue'])->name('generate-due');
                    Route::post('/{billing}/generate-invoice', [BillingController::class, 'generateInvoice'])->name('generate-invoice');
                });
                Route::prefix('payroll')->name('payroll.')->group(function (): void {
                    Route::get('/', [PayrollController::class, 'index'])->name('index');
                    Route::post('/', [PayrollController::class, 'store'])->name('store');
                    Route::patch('/{split}', [PayrollController::class, 'update'])->name('update');
                    Route::delete('/{split}', [PayrollController::class, 'destroy'])->name('destroy');
                    Route::patch('/{split}/items/{item}/status', [PayrollController::class, 'updateItemStatus'])->name('items.status.update');
                });
                Route::prefix('cash-bank')->name('cash-bank.')->group(function (): void {
                    Route::get('/', [CashBankController::class, 'index'])->name('index');
                    Route::post('/', [CashBankController::class, 'store'])->name('store');
                    Route::patch('/{account}', [CashBankController::class, 'update'])->name('update');
                    Route::delete('/{account}', [CashBankController::class, 'destroy'])->name('destroy');
                    Route::post('/transfer', [CashBankController::class, 'transfer'])->name('transfer');
                    Route::patch('/{account}/reconcile', [CashBankController::class, 'reconcile'])->name('reconcile');
                });
                Route::prefix('expenses')->name('expenses.')->group(function (): void {
                    Route::get('/', [ExpenseController::class, 'index'])->name('index');
                    Route::post('/reimbursements', [ExpenseController::class, 'storeReimbursement'])->name('reimbursements.store');
                    Route::patch('/reimbursements/{reimbursement}', [ExpenseController::class, 'updateReimbursement'])->name('reimbursements.update');
                    Route::delete('/reimbursements/{reimbursement}', [ExpenseController::class, 'destroyReimbursement'])->name('reimbursements.destroy');
                    Route::patch('/reimbursements/{reimbursement}/status', [ExpenseController::class, 'updateReimbursementStatus'])->name('reimbursements.status.update');
                    Route::post('/budgets', [ExpenseController::class, 'storeBudget'])->name('budgets.store');
                    Route::patch('/budgets/{budget}', [ExpenseController::class, 'updateBudget'])->name('budgets.update');
                    Route::delete('/budgets/{budget}', [ExpenseController::class, 'destroyBudget'])->name('budgets.destroy');
                });
                Route::prefix('reports')->name('reports.')->group(function (): void {
                    Route::get('/', [FinancialReportController::class, 'index'])->name('index');
                    Route::post('/chart-of-accounts', [FinancialReportController::class, 'storeChart'])->name('chart.store');
                    Route::patch('/chart-of-accounts/{account}', [FinancialReportController::class, 'updateChart'])->name('chart.update');
                    Route::delete('/chart-of-accounts/{account}', [FinancialReportController::class, 'destroyChart'])->name('chart.destroy');
                });
                Route::prefix('transactions')->name('transactions.')->group(function (): void {
                    Route::get('/', [TransactionController::class, 'index'])->name('index');
                    Route::get('/export', [TransactionController::class, 'export'])->name('export');
                });
                Route::prefix('invoices')->name('invoices.')->group(function (): void {
                    Route::get('/', [InvoiceController::class, 'index'])->name('index');
                    Route::post('/', [InvoiceController::class, 'store'])->name('store');
                    Route::patch('/{invoice}', [InvoiceController::class, 'update'])->name('update');
                    Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
                    Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('status.update');
                    Route::post('/{invoice}/approve', [InvoiceController::class, 'approve'])->name('approve');
                    Route::post('/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('payments.store');
                    Route::post('/{invoice}/pakasir-link', [InvoiceController::class, 'generatePakasirLink'])->name('pakasir-link.store');
                });
                Route::prefix('quotations')->name('quotations.')->group(function (): void {
                    Route::get('/', [QuotationController::class, 'index'])->name('index');
                    Route::post('/', [QuotationController::class, 'store'])->name('store');
                    Route::patch('/{quotation}', [QuotationController::class, 'update'])->name('update');
                    Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('destroy');
                    Route::patch('/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('status.update');
                    Route::post('/{quotation}/send', [QuotationController::class, 'send'])->name('send');
                    Route::post('/{quotation}/convert', [QuotationController::class, 'convert'])->name('convert');
                });
                Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
                Route::patch('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
                Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
            });

            Route::prefix('automation')->name('automation.')->group(function (): void {
                Route::get('/', [AutomationController::class, 'index'])->name('index');
                Route::get('/ai-tools', [App\Http\Controllers\App\Automation\AiToolController::class, 'index'])->name('ai-tools.index');
                Route::get('/integrations', [App\Http\Controllers\App\Automation\IntegrationController::class, 'index'])->name('integrations.index');
                Route::get('/api-keys', [App\Http\Controllers\App\Automation\ApiKeyController::class, 'index'])->name('api-keys.index');
                Route::post('/', [AutomationController::class, 'store'])->name('store');
                Route::patch('/{workflow}', [AutomationController::class, 'update'])->name('update');
                Route::patch('/{workflow}/toggle', [AutomationController::class, 'toggle'])->name('toggle');
                Route::post('/{workflow}/run-test', [AutomationController::class, 'runTest'])->name('run-test');
                Route::delete('/{workflow}', [AutomationController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('digital-services')->name('digital-services.')->group(function (): void {
                Route::get('/', [DigitalServiceController::class, 'index'])->name('index');
                Route::post('/websites', [DigitalServiceController::class, 'storeWebsite'])->name('websites.store');
                Route::patch('/websites/{website}', [DigitalServiceController::class, 'updateWebsite'])->name('websites.update');
                Route::delete('/websites/{website}', [DigitalServiceController::class, 'destroyWebsite'])->name('websites.destroy');
                Route::post('/deployments', [DigitalServiceController::class, 'storeDeployment'])->name('deployments.store');
                Route::patch('/deployments/{deployment}', [DigitalServiceController::class, 'updateDeployment'])->name('deployments.update');
                Route::delete('/deployments/{deployment}', [DigitalServiceController::class, 'destroyDeployment'])->name('deployments.destroy');
                Route::post('/domains', [DigitalServiceController::class, 'storeDomain'])->name('domains.store');
                Route::patch('/domains/{domain}', [DigitalServiceController::class, 'updateDomain'])->name('domains.update');
                Route::delete('/domains/{domain}', [DigitalServiceController::class, 'destroyDomain'])->name('domains.destroy');
                Route::post('/servers', [DigitalServiceController::class, 'storeServer'])->name('servers.store');
                Route::patch('/servers/{server}', [DigitalServiceController::class, 'updateServer'])->name('servers.update');
                Route::delete('/servers/{server}', [DigitalServiceController::class, 'destroyServer'])->name('servers.destroy');
                Route::post('/forms', [DigitalServiceController::class, 'storeForm'])->name('forms.store');
                Route::patch('/forms/{form}', [DigitalServiceController::class, 'updateForm'])->name('forms.update');
                Route::delete('/forms/{form}', [DigitalServiceController::class, 'destroyForm'])->name('forms.destroy');
            });

            Route::prefix('marketing')->name('marketing.')->group(function (): void {
                Route::get('/', [MarketingController::class, 'index'])->name('index');
                
                // Campaigns
                Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
                Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
                Route::patch('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
                Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
                Route::post('/campaigns/{campaign}/send-wa', [CampaignController::class, 'sendWhatsApp'])->name('campaigns.send-wa');
                
                // Social Posts
                Route::get('/social-posts/{post}', [SocialPostController::class, 'show'])->name('social-posts.show');
                Route::post('/social-posts', [SocialPostController::class, 'store'])->name('social-posts.store');
                Route::patch('/social-posts/{post}', [SocialPostController::class, 'update'])->name('social-posts.update');
                Route::delete('/social-posts/{post}', [SocialPostController::class, 'destroy'])->name('social-posts.destroy');
                
                // Newsletters
                Route::get('/newsletters/{newsletter}', [NewsletterController::class, 'show'])->name('newsletters.show');
                Route::post('/newsletters', [NewsletterController::class, 'store'])->name('newsletters.store');
                Route::patch('/newsletters/{newsletter}', [NewsletterController::class, 'update'])->name('newsletters.update');
                Route::delete('/newsletters/{newsletter}', [NewsletterController::class, 'destroy'])->name('newsletters.destroy');
                Route::post('/newsletters/{newsletter}/send-wa', [NewsletterController::class, 'sendWhatsApp'])->name('newsletters.send-wa');
                
                // Subscribers
                Route::post('/subscribers', [MarketingController::class, 'storeSubscriber'])->name('subscribers.store');
                Route::patch('/subscribers/{subscriber}', [MarketingController::class, 'updateSubscriber'])->name('subscribers.update');
                Route::delete('/subscribers/{subscriber}', [MarketingController::class, 'destroySubscriber'])->name('subscribers.destroy');
            });

            Route::prefix('system')->name('system.')->group(function (): void {
                Route::get('/', [SystemController::class, 'index'])->name('index');
                Route::get('/executive-hub', [ExecutiveHubController::class, 'index'])->name('executive-hub');
                
                Route::post('/roles', [SystemController::class, 'storeRole'])->name('roles.store');
                Route::patch('/roles/{role}', [SystemController::class, 'updateRole'])->name('roles.update');
                Route::delete('/roles/{role}', [SystemController::class, 'destroyRole'])->name('roles.destroy');
                Route::post('/memberships', [SystemController::class, 'storeMembership'])->name('memberships.store');
                Route::patch('/memberships/{membership}', [SystemController::class, 'updateMembership'])->name('memberships.update');
                Route::delete('/memberships/{membership}', [SystemController::class, 'destroyMembership'])->name('memberships.destroy');
                Route::patch('/settings', [SystemController::class, 'updateSettings'])->name('settings.update');
                Route::post('/audit-logs', [SystemController::class, 'storeAuditLog'])->name('audit-logs.store');
                Route::patch('/audit-logs/{auditLog}', [SystemController::class, 'updateAuditLog'])->name('audit-logs.update');
                Route::delete('/audit-logs/{auditLog}', [SystemController::class, 'destroyAuditLog'])->name('audit-logs.destroy');
                Route::post('/api-keys', [SystemController::class, 'storeApiKey'])->name('api-keys.store');
                Route::patch('/api-keys/{apiKey}', [SystemController::class, 'updateApiKey'])->name('api-keys.update');
                Route::delete('/api-keys/{apiKey}', [SystemController::class, 'destroyApiKey'])->name('api-keys.destroy');
                Route::patch('/security', [SystemController::class, 'updateSecurity'])->name('security.update');
                Route::post('/help-articles', [SystemController::class, 'storeHelpArticle'])->name('help-articles.store');
                Route::patch('/help-articles/{helpArticle}', [SystemController::class, 'updateHelpArticle'])->name('help-articles.update');
                Route::delete('/help-articles/{helpArticle}', [SystemController::class, 'destroyHelpArticle'])->name('help-articles.destroy');
            });

            Route::prefix('communication')->name('communication.')->group(function (): void {
                Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
                Route::post('/inbox', [InboxController::class, 'store'])->name('inbox.store');
                Route::patch('/inbox/{conversation}', [InboxController::class, 'update'])->name('inbox.update');
                Route::delete('/inbox/{conversation}', [InboxController::class, 'destroy'])->name('inbox.destroy');
                Route::post('/inbox/{conversation}/messages', [InboxController::class, 'storeMessage'])->name('inbox.messages.store');
                Route::get('/notifications', NotificationsController::class)->name('notifications');
                Route::post('/notifications', [NotificationsController::class, 'store'])->name('notifications.store');
                Route::patch('/notifications/{notification}', [NotificationsController::class, 'update'])->name('notifications.update');
                Route::delete('/notifications/{notification}', [NotificationsController::class, 'destroy'])->name('notifications.destroy');
                Route::get('/activity-feed', ActivityFeedController::class)->name('activity-feed');
                Route::post('/activity-feed/{activity}/comments', [ActivityFeedController::class, 'storeComment'])->name('activity-feed.comment');
                Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
                Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');
                Route::patch('/calendar/{calendarEvent}', [CalendarController::class, 'update'])->name('calendar.update');
                Route::delete('/calendar/{calendarEvent}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

                Route::prefix('support-tickets')->name('support-tickets.')->group(function (): void {
                    Route::get('/', [App\Http\Controllers\App\Communication\SupportTicketController::class, 'index'])->name('index');
                    Route::post('/', [App\Http\Controllers\App\Communication\SupportTicketController::class, 'store'])->name('store');
                    Route::patch('/{supportTicket}', [App\Http\Controllers\App\Communication\SupportTicketController::class, 'update'])->name('update');
                    Route::delete('/{supportTicket}', [App\Http\Controllers\App\Communication\SupportTicketController::class, 'destroy'])->name('destroy');
                    Route::post('/{supportTicket}/send-wa', [App\Http\Controllers\App\Communication\SupportTicketController::class, 'sendWhatsApp'])->name('send-wa');
                });
            });
        });
});
