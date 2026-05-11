<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreTaskCommentRequest;
use App\Http\Requests\Project\StoreTaskTimeLogRequest;
use App\Http\Requests\Project\UpdateTaskStatusRequest;
use App\Http\Requests\Project\UpsertTaskRequest;
use App\Http\Requests\Project\UpsertTaskTemplateRequest;
use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\Workspace;
use App\Modules\Project\Tasks\Queries\TaskIndexQuery;
use App\Services\Project\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class TaskController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, TaskIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Tasks/Index',
            title: 'Tasks',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(UpsertTaskRequest $request, Workspace $workspace, TaskService $service): RedirectResponse
    {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Task created successfully.');
    }

    public function update(UpsertTaskRequest $request, Workspace $workspace, Task $task, TaskService $service): RedirectResponse
    {
        $service->update($workspace, $task, $request->validated());

        return back()->with('success', 'Task updated successfully.');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Workspace $workspace, Task $task, TaskService $service): RedirectResponse
    {
        $service->updateStatus($workspace, $task, $request->validated('status'));

        return back()->with('success', 'Task status updated successfully.');
    }

    public function storeTimeLog(
        StoreTaskTimeLogRequest $request,
        Workspace $workspace,
        Task $task,
        TaskService $service
    ): RedirectResponse {
        $service->addTimeLog($workspace, $task, $request->validated());

        return back()->with('success', 'Task time log added successfully.');
    }

    public function storeComment(
        StoreTaskCommentRequest $request,
        Workspace $workspace,
        Task $task,
        TaskService $service
    ): RedirectResponse {
        $service->addComment($workspace, $task, $request->validated('content'));

        return back()->with('success', 'Task comment added successfully.');
    }

    public function destroy(Workspace $workspace, Task $task, TaskService $service): RedirectResponse
    {
        $service->delete($workspace, $task);

        return back()->with('success', 'Task deleted successfully.');
    }

    public function storeTemplate(
        UpsertTaskTemplateRequest $request,
        Workspace $workspace,
        TaskService $service
    ): RedirectResponse {
        $service->createTemplate($workspace, $request->validated());

        return back()->with('success', 'Task template created successfully.');
    }

    public function updateTemplate(
        UpsertTaskTemplateRequest $request,
        Workspace $workspace,
        TaskTemplate $template,
        TaskService $service
    ): RedirectResponse {
        $service->updateTemplate($workspace, $template, $request->validated());

        return back()->with('success', 'Task template updated successfully.');
    }

    public function destroyTemplate(
        Workspace $workspace,
        TaskTemplate $template,
        TaskService $service
    ): RedirectResponse {
        $service->deleteTemplate($workspace, $template);

        return back()->with('success', 'Task template deleted successfully.');
    }
}
