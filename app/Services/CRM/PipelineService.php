<?php

namespace App\Services\CRM;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PipelineService
{
    public function create(Workspace $workspace, array $data): Pipeline
    {
        return DB::transaction(function () use ($workspace, $data): Pipeline {
            if (($data['is_default'] ?? false) === true) {
                $this->clearDefaultPipeline($workspace);
            }

            $pipeline = Pipeline::query()->create([
                'workspace_id' => $workspace->getKey(),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_default' => (bool) ($data['is_default'] ?? false),
            ]);

            $this->syncStages($pipeline, collect($data['stages']));

            return $pipeline->load('stages');
        });
    }

    public function update(Workspace $workspace, Pipeline $pipeline, array $data): Pipeline
    {
        abort_unless($pipeline->workspace_id === $workspace->getKey(), 404);

        return DB::transaction(function () use ($workspace, $pipeline, $data): Pipeline {
            if (($data['is_default'] ?? false) === true) {
                $this->clearDefaultPipeline($workspace, $pipeline->getKey());
            }

            $pipeline->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_default' => (bool) ($data['is_default'] ?? false),
            ]);

            $this->syncStages($pipeline, collect($data['stages']));

            return $pipeline->refresh()->load('stages');
        });
    }

    public function delete(Workspace $workspace, Pipeline $pipeline): void
    {
        abort_unless($pipeline->workspace_id === $workspace->getKey(), 404);

        DB::transaction(function () use ($workspace, $pipeline): void {
            $wasDefault = $pipeline->is_default;

            $pipeline->delete();

            if ($wasDefault) {
                $nextPipeline = Pipeline::query()
                    ->where('workspace_id', $workspace->getKey())
                    ->orderBy('name')
                    ->first();

                if ($nextPipeline) {
                    $nextPipeline->update(['is_default' => true]);
                }
            }
        });
    }

    protected function clearDefaultPipeline(Workspace $workspace, ?string $exceptId = null): void
    {
        Pipeline::query()
            ->where('workspace_id', $workspace->getKey())
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }

    protected function syncStages(Pipeline $pipeline, Collection $stages): void
    {
        $currentStages = $pipeline->stages()->get()->keyBy(fn (PipelineStage $stage) => $stage->getKey());
        $keepIds = [];

        foreach ($stages->values() as $index => $stageData) {
            $stageId = filled($stageData['id'] ?? null) ? (string) $stageData['id'] : null;
            $payload = [
                'name' => $stageData['name'],
                'order_index' => $index + 1,
                'color' => $stageData['color'] ?? null,
                'is_won' => (bool) ($stageData['is_won'] ?? false),
                'is_lost' => (bool) ($stageData['is_lost'] ?? false),
            ];

            if ($stageId && $currentStages->has($stageId)) {
                $stage = $currentStages->get($stageId);
                $stage->update($payload);
                $keepIds[] = $stage->getKey();

                continue;
            }

            $stage = $pipeline->stages()->create($payload);

            $keepIds[] = $stage->getKey();
        }

        $pipeline->stages()
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
