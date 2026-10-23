<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $unreadCount = DB::table('notifications')
            ->where('to_id', $userId)
            ->where('seen', 0)
            ->count();

        $notifications = $this->notificationsFor($userId)
            ->orderByDesc('notifications.created_at')
            ->orderByDesc('notifications.id')
            ->paginate(20);

        $notifications->getCollection()->transform(fn ($notification) => [
            'id' => $notification->id,
            'content' => $notification->content,
            'seen' => (bool) $notification->seen,
            'created_at' => $notification->created_at,
            'from' => [
                'id' => $notification->from_id,
                'username' => $notification->username,
            ],
            'url' => $this->supportUrl(
                $notification->message_thread_id ?? $notification->access_thread_id,
                $notification->message_thread_type ?? $notification->access_thread_type,
            ),
        ]);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function read(Request $request, int $notification): JsonResponse
    {
        $item = $this->notificationsFor($request->user()->id)
            ->where('notifications.id', $notification)
            ->first();

        abort_if($item === null, 404);

        DB::table('notifications')->where('id', $notification)->update([
            'seen' => 1,
            'updated_at' => now(),
        ]);

        return response()->json([
            'read' => true,
            'url' => $this->supportUrl(
                $item->message_thread_id ?? $item->access_thread_id,
                $item->message_thread_type ?? $item->access_thread_type,
            ),
        ]);
    }

    private function notificationsFor(int $userId)
    {
        return DB::table('notifications')
            ->join('users', 'users.id', '=', 'notifications.from_id')
            ->leftJoin('support_notification_links', 'support_notification_links.notification_id', '=', 'notifications.id')
            ->leftJoin('support_messages', 'support_messages.id', '=', 'support_notification_links.message_id')
            ->leftJoin('support_threads as message_threads', 'message_threads.id', '=', 'support_messages.thread_id')
            ->leftJoin('support_access_notification_links', 'support_access_notification_links.notification_id', '=', 'notifications.id')
            ->leftJoin('support_access_requests', 'support_access_requests.id', '=', 'support_access_notification_links.access_request_id')
            ->leftJoin('support_threads as access_threads', 'access_threads.id', '=', 'support_access_requests.thread_id')
            ->where('notifications.to_id', $userId)
            ->select([
                'notifications.id',
                'notifications.from_id',
                'notifications.content',
                'notifications.seen',
                'notifications.created_at',
                'users.username',
                'message_threads.id as message_thread_id',
                'message_threads.type as message_thread_type',
                'access_threads.id as access_thread_id',
                'access_threads.type as access_thread_type',
            ]);
    }

    private function supportUrl(?int $threadId, ?string $type): ?string
    {
        if ($threadId === null) {
            return null;
        }

        return ($type === 'bug' ? '/bugs' : '/suggestions').'?thread='.$threadId;
    }
}
