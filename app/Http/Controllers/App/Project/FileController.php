<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\UpdateFileApprovalRequest;
use App\Http\Requests\Project\UpdateFileShareRequest;
use App\Http\Requests\Project\UpsertFileFolderRequest;
use App\Http\Requests\Project\UpsertFileRequest;
use App\Models\File;
use App\Models\FileFolder;
use App\Models\Workspace;
use App\Modules\Project\Files\Queries\FileIndexQuery;
use App\Services\Project\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class FileController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, FileIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Files/Index',
            title: 'Files',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(UpsertFileRequest $request, Workspace $workspace, FileService $service): RedirectResponse
    {
        $service->create($workspace, $request->validated(), $request->file('binary'));

        return back()->with('success', 'File uploaded successfully.');
    }

    public function update(
        UpsertFileRequest $request,
        Workspace $workspace,
        File $file,
        FileService $service
    ): RedirectResponse {
        $service->update($workspace, $file, $request->validated(), $request->file('binary'));

        return back()->with('success', 'File updated successfully.');
    }

    public function destroy(Workspace $workspace, File $file, FileService $service): RedirectResponse
    {
        $service->delete($workspace, $file);

        return back()->with('success', 'File deleted successfully.');
    }

    public function updateApproval(
        UpdateFileApprovalRequest $request,
        Workspace $workspace,
        File $file,
        FileService $service
    ): RedirectResponse {
        $service->updateApproval($workspace, $file, $request->validated()['approval_status']);

        return back()->with('success', 'Approval status updated successfully.');
    }

    public function updateShare(
        UpdateFileShareRequest $request,
        Workspace $workspace,
        File $file,
        FileService $service
    ): RedirectResponse {
        $service->updateShare($workspace, $file, $request->validated());

        return back()->with('success', 'Share settings updated successfully.');
    }

    public function storeFolder(
        UpsertFileFolderRequest $request,
        Workspace $workspace,
        FileService $service
    ): RedirectResponse {
        $service->createFolder($workspace, $request->validated());

        return back()->with('success', 'Folder created successfully.');
    }

    public function updateFolder(
        UpsertFileFolderRequest $request,
        Workspace $workspace,
        FileFolder $folder,
        FileService $service
    ): RedirectResponse {
        $service->updateFolder($workspace, $folder, $request->validated());

        return back()->with('success', 'Folder updated successfully.');
    }

    public function destroyFolder(
        Workspace $workspace,
        FileFolder $folder,
        FileService $service
    ): RedirectResponse {
        $service->deleteFolder($workspace, $folder);

        return back()->with('success', 'Folder deleted successfully.');
    }
}
