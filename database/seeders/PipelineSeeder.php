<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class PipelineSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Web Development',
                'description' => 'Pipeline for website and application projects.',
                'is_default' => true,
            ],
            [
                'name' => 'Social Media',
                'description' => 'Pipeline for social media management services.',
                'is_default' => false,
            ],
        ];

        $stageDefinitions = [
            ['name' => 'New', 'order_index' => 1, 'color' => '#94A3B8', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Contacted', 'order_index' => 2, 'color' => '#0EA5E9', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Proposal', 'order_index' => 3, 'color' => '#8B5CF6', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Negotiation', 'order_index' => 4, 'color' => '#F59E0B', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Won', 'order_index' => 5, 'color' => '#10B981', 'is_won' => true, 'is_lost' => false],
            ['name' => 'Lost', 'order_index' => 6, 'color' => '#EF4444', 'is_won' => false, 'is_lost' => true],
        ];

        Workspace::query()->each(function (Workspace $workspace) use ($definitions, $stageDefinitions): void {
            foreach ($definitions as $definition) {
                $pipeline = Pipeline::query()->updateOrCreate(
                    [
                        'workspace_id' => $workspace->getKey(),
                        'name' => $definition['name'],
                    ],
                    $definition + ['workspace_id' => $workspace->getKey()],
                );

                foreach ($stageDefinitions as $stageDefinition) {
                    PipelineStage::query()->updateOrCreate(
                        [
                            'pipeline_id' => $pipeline->getKey(),
                            'order_index' => $stageDefinition['order_index'],
                        ],
                        $stageDefinition + ['pipeline_id' => $pipeline->getKey()],
                    );
                }
            }
        });
    }
}
