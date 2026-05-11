<?php

use App\Http\Controllers\App\Communication\ActivityFeedController;
use App\Http\Controllers\App\Communication\CalendarController;
use App\Http\Controllers\App\Communication\InboxController;
use App\Http\Controllers\App\CRM\ClientController;
use App\Http\Controllers\App\CRM\LeadController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\Finance\FinanceOverviewController;
use App\Http\Controllers\App\Project\ProjectController;
use App\Http\Controllers\App\Project\ContractController;
use App\Http\Controllers\App\Project\ContractTemplateController;
use App\Http\Controllers\App\Project\FileController;
use App\Http\Controllers\App\Project\MeetingController;
use App\Http\Controllers\App\Project\NoteController;
use App\Http\Controllers\App\Project\ProjectTemplateController;
use App\Http\Controllers\App\Project\TaskController;
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

    Route::prefix('w/{workspace:slug}')
        ->middleware(['setWorkspace', 'ensureWorkspaceAccess'])
        ->name('workspace.')
        ->group(function (): void {
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
                Route::get('/', FinanceOverviewController::class)->name('overview');
            });

            Route::prefix('communication')->name('communication.')->group(function (): void {
                Route::get('/inbox', InboxController::class)->name('inbox');
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
                });
            });
        });
});
