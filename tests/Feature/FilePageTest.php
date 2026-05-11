<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\File;
use App\Models\FileFolder;
use App\Models\Project;
use App\Models\Workspace;
use App\Support\Tenancy\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class FilePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WorkspaceContext::clear();
        Storage::fake('public');
    }

    public function test_file_index_page_returns_brief_aligned_payload(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->fileContext();
        $project = $this->createProject($workspace, $owner->getKey(), 'Asset Rollout');
        $client = $this->createClient($workspace, 'PT Asset Cipta');
        $folder = FileFolder::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Brand Assets',
        ]);

        $file = File::query()->create([
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'client_id' => $client->getKey(),
            'folder_id' => $folder->getKey(),
            'name' => 'Hero Banner',
            'original_name' => 'hero-banner.png',
            'path' => 'files/velora/hero-banner.png',
            'mime_type' => 'image/png',
            'size_bytes' => 2048,
            'version' => 1,
            'approval_status' => 'pending',
            'share_token' => 'share-token',
            'share_expires_at' => now()->addDays(2),
            'uploaded_by' => $owner->getKey(),
            'created_at' => now(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('workspace.files.index', [
                'workspace' => $workspace,
                'preview' => 'image',
            ]));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Files/Index')
                ->where('workspace.slug', 'velora')
                ->where('filters.preview', 'image')
                ->where('files.summary.total_files', 1)
                ->where('files.items.0.id', $file->getKey())
                ->where('files.items.0.project.name', 'Asset Rollout')
                ->where('files.items.0.client.name', 'PT Asset Cipta')
                ->where('files.items.0.folder.name', 'Brand Assets')
                ->where('files.items.0.preview_kind', 'image')
                ->where('files.items.0.share_active', true)
                ->has('folders', 1)
                ->has('filterOptions.projects', 1)
                ->has('filterOptions.clients', 1)
            );
    }

    public function test_file_can_be_uploaded_with_project_client_and_folder(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->fileContext();
        $project = $this->createProject($workspace, $owner->getKey(), 'Upload Project');
        $client = $this->createClient($workspace, 'PT Upload Nusantara');
        $folder = FileFolder::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Deliverables',
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.files.store', $workspace), [
                'project_id' => $project->getKey(),
                'client_id' => $client->getKey(),
                'folder_id' => $folder->getKey(),
                'name' => 'Homepage Final',
                'approval_status' => 'pending',
                'binary' => UploadedFile::fake()->image('homepage-final.png', 1600, 900),
            ]);

        $response->assertRedirect();

        $file = File::query()->where('name', 'Homepage Final')->firstOrFail();

        $this->assertDatabaseHas('files', [
            'id' => $file->getKey(),
            'workspace_id' => $workspace->getKey(),
            'project_id' => $project->getKey(),
            'client_id' => $client->getKey(),
            'folder_id' => $folder->getKey(),
            'version' => 1,
            'approval_status' => 'pending',
        ]);

        Storage::disk('public')->assertExists($file->path);
    }

    public function test_new_version_can_be_uploaded_for_existing_file_family(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->fileContext();

        $root = File::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => 'Deck Presentation',
            'original_name' => 'deck-v1.pdf',
            'path' => 'files/velora/deck-v1.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 3000,
            'version' => 1,
            'approval_status' => 'pending',
            'uploaded_by' => $owner->getKey(),
            'created_at' => now()->subDay(),
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('workspace.files.store', $workspace), [
                'name' => 'Deck Presentation',
                'source_file_id' => $root->getKey(),
                'approval_status' => 'pending',
                'binary' => UploadedFile::fake()->create('deck-v2.pdf', 1280, 'application/pdf'),
            ]);

        $response->assertRedirect();

        $version = File::query()
            ->where('parent_file_id', $root->getKey())
            ->firstOrFail();

        $this->assertSame(2, $version->version);
        $this->assertSame($root->getKey(), $version->parent_file_id);
        Storage::disk('public')->assertExists($version->path);
    }

    public function test_file_share_and_approval_can_be_updated_and_public_link_serves_file(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->fileContext();

        $upload = UploadedFile::fake()->image('approval-asset.png', 1200, 630);

        $this
            ->actingAs($owner)
            ->post(route('workspace.files.store', $workspace), [
                'name' => 'Approval Asset',
                'approval_status' => 'pending',
                'binary' => $upload,
            ])
            ->assertRedirect();

        $file = File::query()->where('name', 'Approval Asset')->firstOrFail();

        $this
            ->actingAs($owner)
            ->patch(route('workspace.files.approval.update', [
                'workspace' => $workspace,
                'file' => $file,
            ]), [
                'approval_status' => 'approved',
            ])
            ->assertRedirect();

        $this
            ->actingAs($owner)
            ->patch(route('workspace.files.share.update', [
                'workspace' => $workspace,
                'file' => $file,
            ]), [
                'share_expires_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'regenerate' => true,
            ])
            ->assertRedirect();

        $file = $file->fresh();

        $this->assertSame('approved', $file->approval_status);
        $this->assertNotNull($file->approved_by);
        $this->assertNotNull($file->share_token);
        $this->assertNotNull($file->share_expires_at);

        $this
            ->get(route('files.public.show', ['token' => $file->share_token]))
            ->assertOk();
    }

    public function test_folder_can_be_created_updated_and_deleted(): void
    {
        $this->seed();

        [$owner, $workspace] = $this->fileContext();

        $create = $this
            ->actingAs($owner)
            ->post(route('workspace.files.folders.store', $workspace), [
                'name' => 'Creative Packs',
            ]);

        $create->assertRedirect();

        $folder = FileFolder::query()->where('name', 'Creative Packs')->firstOrFail();

        $update = $this
            ->actingAs($owner)
            ->patch(route('workspace.files.folders.update', [
                'workspace' => $workspace,
                'folder' => $folder,
            ]), [
                'name' => 'Creative Packs Updated',
            ]);

        $update->assertRedirect();

        $this->assertDatabaseHas('file_folders', [
            'id' => $folder->getKey(),
            'name' => 'Creative Packs Updated',
        ]);

        $delete = $this
            ->actingAs($owner)
            ->delete(route('workspace.files.folders.destroy', [
                'workspace' => $workspace,
                'folder' => $folder,
            ]));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('file_folders', [
            'id' => $folder->getKey(),
        ]);
    }

    /**
     * @return array{0: \App\Models\User, 1: Workspace}
     */
    protected function fileContext(): array
    {
        $workspace = Workspace::query()->where('slug', 'velora')->firstOrFail();
        $owner = \App\Models\User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();

        return [$owner, $workspace];
    }

    protected function createProject(Workspace $workspace, string $userId, string $name): Project
    {
        return Project::query()->create([
            'workspace_id' => $workspace->getKey(),
            'name' => $name,
            'status' => 'active',
            'created_by' => $userId,
        ]);
    }

    protected function createClient(Workspace $workspace, string $name): Client
    {
        return Client::query()->create([
            'workspace_id' => $workspace->getKey(),
            'company_name' => $name,
            'status' => 'active',
            'portal_access' => true,
        ]);
    }
}
