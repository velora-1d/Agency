<?php

namespace Database\Seeders;

use App\Models\ActivityFeed;
use App\Models\ActivityComment;
use App\Models\Workspace;
use App\Models\User;
use App\Models\Project;
use App\Models\Lead;
use App\Models\Invoice;
use App\Models\Task;
use App\Models\Meeting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ActivityFeedSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::first();
        if (!$workspace) return;

        $users = $workspace->users;
        if ($users->isEmpty()) return;

        $activityTypes = [
            'project_created' => 'Created a new project: :subject',
            'lead_converted' => 'Converted lead :subject to client',
            'invoice_paid' => 'Invoice :subject has been paid',
            'task_completed' => 'Completed task: :subject',
            'meeting_scheduled' => 'Scheduled a new meeting: :subject',
        ];

        $projects = Project::where('workspace_id', $workspace->id)->get();
        $leads = Lead::where('workspace_id', $workspace->id)->get();
        $invoices = Invoice::where('workspace_id', $workspace->id)->get();
        $tasks = Task::where('workspace_id', $workspace->id)->get();
        $meetings = Meeting::where('workspace_id', $workspace->id)->get();

        for ($i = 0; $i < 30; $i++) {
            $type = array_rand($activityTypes);
            $user = $users->random();
            $createdAt = now()->subHours(rand(1, 168)); // up to 7 days ago

            $subject = null;
            if (str_contains($type, 'project') && $projects->isNotEmpty()) {
                $subject = $projects->random();
            } elseif (str_contains($type, 'lead') && $leads->isNotEmpty()) {
                $subject = $leads->random();
            } elseif (str_contains($type, 'invoice') && $invoices->isNotEmpty()) {
                $subject = $invoices->random();
            } elseif (str_contains($type, 'task') && $tasks->isNotEmpty()) {
                $subject = $tasks->random();
            } elseif (str_contains($type, 'meeting') && $meetings->isNotEmpty()) {
                $subject = $meetings->random();
            }

            $subjectName = $subject ? ($subject->name ?? $subject->title ?? $subject->number) : 'Sample ' . Str::title(explode('_', $type)[0]);
            
            $activity = ActivityFeed::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'type' => explode('_', $type)[0],
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject ? $subject->id : null,
                'description' => str_replace(':subject', $subjectName, $activityTypes[$type]),
                'metadata' => [
                    'icon' => $this->getIconForType($type),
                    'color' => $this->getColorForType($type),
                ],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Add some comments
            if (rand(0, 1)) {
                $commentCount = rand(1, 4);
                for ($j = 0; $j < $commentCount; $j++) {
                    $commentCreatedAt = $activity->created_at->copy()->addMinutes(rand(5, 120));
                    ActivityComment::create([
                        'activity_id' => $activity->id,
                        'user_id' => $users->random()->id,
                        'content' => $this->getSampleComment($j),
                        'created_at' => $commentCreatedAt,
                        'updated_at' => $commentCreatedAt,
                    ]);
                }
            }
        }
    }

    private function getSampleComment(int $index): string
    {
        $comments = [
            'Great progress on this! Keep it up.',
            'Need to review the details later.',
            'Looks good to me. Approved.',
            'Can we discuss this in the next meeting?',
            'Already handled. Thanks!',
            'Is there any update on this?',
        ];
        return $comments[$index % count($comments)];
    }

    private function getIconForType(string $type): string
    {
        return match ($type) {
            'project_created' => 'FolderPlus',
            'lead_converted' => 'UserCheck',
            'invoice_paid' => 'CreditCard',
            'task_completed' => 'CheckCircle',
            'meeting_scheduled' => 'Calendar',
            default => 'Activity',
        };
    }

    private function getColorForType(string $type): string
    {
        return match ($type) {
            'project_created' => 'blue',
            'lead_converted' => 'green',
            'invoice_paid' => 'emerald',
            'task_completed' => 'purple',
            'meeting_scheduled' => 'orange',
            default => 'slate',
        };
    }
}
