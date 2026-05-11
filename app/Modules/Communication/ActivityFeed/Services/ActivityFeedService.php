<?php

namespace App\Modules\Communication\ActivityFeed\Services;

use App\Models\ActivityFeed;
use App\Models\ActivityComment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityFeedService
{
    public function addComment(ActivityFeed $activity, array $data): ActivityComment
    {
        $comment = ActivityComment::create([
            'activity_id' => $activity->id,
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        \App\Events\CommentAdded::dispatch($comment);

        return $comment;
    }
}
