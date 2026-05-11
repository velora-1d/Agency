<?php

namespace App\Http\Controllers\App\Project;

use App\Http\Controllers\App\Concerns\BuildsAppShellResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\UpsertNoteFolderRequest;
use App\Http\Requests\Project\UpsertNoteRequest;
use App\Models\Note;
use App\Models\NoteFolder;
use App\Models\Workspace;
use App\Modules\Project\Notes\Queries\NoteIndexQuery;
use App\Services\Project\NoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class NoteController extends Controller
{
    use BuildsAppShellResponse;

    public function index(Request $request, Workspace $workspace, NoteIndexQuery $query): Response
    {
        return $this->appShell(
            workspace: $workspace,
            screen: 'Notes/Index',
            title: 'Notes',
            payload: $query->getIndexPayload($workspace, $request->all()),
        );
    }

    public function store(UpsertNoteRequest $request, Workspace $workspace, NoteService $service): RedirectResponse
    {
        $service->create($workspace, $request->validated());

        return back()->with('success', 'Note created successfully.');
    }

    public function update(UpsertNoteRequest $request, Workspace $workspace, Note $note, NoteService $service): RedirectResponse
    {
        $service->update($workspace, $note, $request->validated());

        return back()->with('success', 'Note updated successfully.');
    }

    public function destroy(Workspace $workspace, Note $note, NoteService $service): RedirectResponse
    {
        $service->delete($workspace, $note);

        return back()->with('success', 'Note deleted successfully.');
    }

    public function storeFolder(
        UpsertNoteFolderRequest $request,
        Workspace $workspace,
        NoteService $service
    ): RedirectResponse {
        $service->createFolder($workspace, $request->validated());

        return back()->with('success', 'Folder created successfully.');
    }

    public function updateFolder(
        UpsertNoteFolderRequest $request,
        Workspace $workspace,
        NoteFolder $folder,
        NoteService $service
    ): RedirectResponse {
        $service->updateFolder($workspace, $folder, $request->validated());

        return back()->with('success', 'Folder updated successfully.');
    }

    public function destroyFolder(
        Workspace $workspace,
        NoteFolder $folder,
        NoteService $service
    ): RedirectResponse {
        $service->deleteFolder($workspace, $folder);

        return back()->with('success', 'Folder deleted successfully.');
    }
}
