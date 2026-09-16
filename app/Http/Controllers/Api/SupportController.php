<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $input = $request->validate([
            'type' => ['nullable', Rule::in(['suggestion', 'bug'])],
            'mine' => ['sometimes', 'boolean'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $user = $request->user('sanctum');
        $query = SupportThread::query();
        $mine = $request->boolean('mine');

        if ($mine) {
            abort_if($user === null, 401);
            $query->where('user_id', $user->id);
        }

        if (! $mine && ($user === null || (int) $user->role !== User::ROLE_DEVELOPER)) {
            $query->where(function ($query) use ($user) {
                $query->whereNotExists(function ($review) {
                    $review->selectRaw('1')
                        ->from('support_submission_reviews')
                        ->whereColumn('support_submission_reviews.thread_id', 'support_threads.id')
                        ->whereIn('support_submission_reviews.status', ['pending', 'refused']);
                });
                if ($user !== null) {
                    $query->orWhere('support_threads.user_id', $user->id);
                }
            });
        }

        if (! empty($input['type'])) {
            $query->where('type', $input['type']);
        }

        $query->with('author:id,username')->withMax('messages', 'id')->addSelect([
            'submission_review_status' => DB::table('support_submission_reviews')
                ->select('status')
                ->whereColumn('thread_id', 'support_threads.id'),
            'submission_review_message' => DB::table('support_submission_reviews')
                ->select('response_message')
                ->whereColumn('thread_id', 'support_threads.id'),
        ]);

        if ($user !== null) {
            $query->addSelect([
                'last_read_message_id' => DB::table('support_reads')
                    ->select('last_read_message_id')
                    ->whereColumn('thread_id', 'support_threads.id')
                    ->where('user_id', $user->id),
                'access_request_status' => DB::table('support_access_requests')
                    ->select('status')
                    ->whereColumn('thread_id', 'support_threads.id')
                    ->where('user_id', $user->id),
                'access_request_message' => DB::table('support_access_requests')
                    ->select('message')
                    ->whereColumn('thread_id', 'support_threads.id')
                    ->where('user_id', $user->id),
                'access_response_message' => DB::table('support_access_requests')
                    ->select('response_message')
                    ->whereColumn('thread_id', 'support_threads.id')
                    ->where('user_id', $user->id),
            ]);
        }

        $threads = $query->orderByDesc('updated_at')->orderByDesc('id')->paginate(25);
        $threads->getCollection()->transform(function ($thread) use ($user) {
            $developer = $user !== null && (int) $user->role === User::ROLE_DEVELOPER;
            $owner = $user !== null && (int) $thread->user_id === (int) $user->id;
            $approved = $user !== null && $thread->access_request_status === 'approved';
            $thread->approval_status = $thread->submission_review_status ?? 'approved';
            $thread->approval_message = $thread->submission_review_message;
            $thread->can_reply = $thread->approval_status === 'approved' && ($developer || $owner || $approved);
            $thread->unread = $thread->can_reply
                && (int) $thread->messages_max_id > (int) $thread->last_read_message_id;
            $thread->access_request = $thread->access_request_status === null ? null : [
                'status' => $thread->access_request_status,
                'message' => $thread->access_request_message,
                'response_message' => $thread->access_response_message,
            ];
            unset(
                $thread->messages_max_id,
                $thread->last_read_message_id,
                $thread->access_request_status,
                $thread->access_request_message,
                $thread->access_response_message,
                $thread->submission_review_status,
                $thread->submission_review_message,
            );

            return $thread;
        });

        return response()->json($threads);
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->validate([
            'type' => ['required', Rule::in(['suggestion', 'bug'])],
            'subject' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $thread = DB::transaction(function () use ($request, $input) {
            $thread = new SupportThread;
            $thread->forceFill([
                'user_id' => $request->user()->id,
                'type' => $input['type'],
                'subject' => $input['subject'],
                'status' => 'open',
            ])->save();
            DB::table('support_submission_reviews')->insert([
                'thread_id' => $thread->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->addMessage($thread, $request->user(), $input['content']);

            return $thread;
        });

        $thread->approval_status = 'pending';

        return response()->json($thread->load('author:id,username'), 201);
    }

    public function show(Request $request, SupportThread $thread): JsonResponse
    {
        $input = $request->validate(['before' => ['sometimes', 'integer', 'min:1']]);
        $user = $request->user('sanctum');
        $review = $this->submissionReview($thread);
        if ($review->status !== 'approved') {
            abort_unless($user !== null && ((int) $user->role === User::ROLE_DEVELOPER || (int) $thread->user_id === (int) $user->id), 404);
        }
        $query = $thread->messages()->with('author:id,username');
        if (isset($input['before'])) {
            $query->where('id', '<', $input['before']);
        }
        $messages = $query->orderByDesc('id')->limit(51)->get();
        $hasOlder = $messages->count() > 50;

        return response()->json([
            'thread' => $thread->load('author:id,username'),
            'messages' => $messages->take(50)->reverse()->values(),
            'has_older' => $hasOlder,
            'can_reply' => $review->status === 'approved' && $user !== null && $this->canAccess($thread, $user),
            'approval_status' => $review->status,
            'approval_message' => $review->response_message,
        ]);
    }

    public function reply(Request $request, SupportThread $thread): JsonResponse
    {
        $this->authorizeThread($request, $thread);
        abort_if($this->submissionReview($thread)->status !== 'approved', 409, 'This submission has not been approved.');
        $input = $request->validate(['content' => ['required', 'string', 'max:10000']]);

        $message = DB::transaction(function () use ($thread, $request, $input) {
            $thread = SupportThread::whereKey($thread->id)->lockForUpdate()->firstOrFail();
            abort_if($thread->status === 'closed', 409, 'This thread is closed.');

            return $this->addMessage($thread, $request->user(), $input['content']);
        });

        return response()->json($message->load('author:id,username'), 201);
    }

    public function update(Request $request, SupportThread $thread): JsonResponse
    {
        $this->authorizeThread($request, $thread);
        abort_unless((int) $request->user()->role === User::ROLE_DEVELOPER, 403);
        abort_if($this->submissionReview($thread)->status !== 'approved', 409, 'This submission has not been approved.');
        $input = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
        ]);

        DB::transaction(function () use ($request, $thread, $input) {
            $thread = SupportThread::whereKey($thread->id)->lockForUpdate()->firstOrFail();
            if ($thread->status !== $input['status']) {
                $thread->status = $input['status'];
                $thread->save();
                $this->addMessage($thread, $request->user(), 'Status changed to '.str_replace('_', ' ', $input['status']).'.', 'status');
            }
        });

        return response()->json($thread->fresh()->load('author:id,username'));
    }

    public function read(Request $request, SupportThread $thread): JsonResponse
    {
        $this->authorizeThread($request, $thread);
        $input = $request->validate([
            'message_id' => ['required', 'integer', Rule::exists('support_messages', 'id')->where('thread_id', $thread->id)],
        ]);

        DB::transaction(function () use ($request, $thread, $input) {
            SupportThread::whereKey($thread->id)->lockForUpdate()->firstOrFail();
            $this->markRead($thread, $request->user(), $input['message_id']);
        });

        return response()->json(['read' => true]);
    }

    public function requests(Request $request): JsonResponse
    {
        abort_unless((int) $request->user()->role === User::ROLE_DEVELOPER, 403);

        $submissions = DB::table('support_submission_reviews')
            ->join('support_threads', 'support_threads.id', '=', 'support_submission_reviews.thread_id')
            ->join('users', 'users.id', '=', 'support_threads.user_id')
            ->where('support_submission_reviews.status', 'pending')
            ->orderBy('support_submission_reviews.created_at')
            ->select([
                'support_threads.id',
                'support_threads.type',
                'support_threads.subject',
                'support_threads.created_at',
                'users.id as user_id',
                'users.username',
            ])->get()->map(fn ($item) => [
                'id' => $item->id,
                'type' => $item->type,
                'subject' => $item->subject,
                'created_at' => $item->created_at,
                'author' => ['id' => $item->user_id, 'username' => $item->username],
            ]);

        $accessRequests = DB::table('support_access_requests')
            ->join('support_threads', 'support_threads.id', '=', 'support_access_requests.thread_id')
            ->join('users', 'users.id', '=', 'support_access_requests.user_id')
            ->where('support_access_requests.status', 'pending')
            ->orderBy('support_access_requests.created_at')
            ->select([
                'support_access_requests.id',
                'support_access_requests.message',
                'support_access_requests.created_at',
                'support_threads.id as thread_id',
                'support_threads.type',
                'support_threads.subject',
                'users.id as user_id',
                'users.username',
            ])->get()->map(fn ($item) => [
                'id' => $item->id,
                'message' => $item->message,
                'created_at' => $item->created_at,
                'thread' => ['id' => $item->thread_id, 'type' => $item->type, 'subject' => $item->subject],
                'user' => ['id' => $item->user_id, 'username' => $item->username],
            ]);

        return response()->json([
            'submissions' => $submissions,
            'access_requests' => $accessRequests,
        ]);
    }

    public function reviewSubmission(Request $request, SupportThread $thread): JsonResponse
    {
        abort_unless((int) $request->user()->role === User::ROLE_DEVELOPER, 403);
        $input = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'refused'])],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $result = DB::transaction(function () use ($request, $thread, $input) {
            $review = DB::table('support_submission_reviews')
                ->where('thread_id', $thread->id)
                ->lockForUpdate()
                ->first();
            abort_if($review === null, 404);
            abort_if($review->status !== 'pending', 409, 'This submission has already been reviewed.');

            DB::table('support_submission_reviews')->where('thread_id', $thread->id)->update([
                'status' => $input['decision'],
                'response_message' => $input['message'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);
            $thread->touch();

            if ((int) $thread->user_id !== (int) $request->user()->id) {
                $notificationId = $this->createNotification(
                    $request->user(),
                    $thread->user_id,
                    'Submission '.$input['decision'].' for Support #'.$thread->id.': '.$thread->subject,
                );
                $messageId = DB::table('support_messages')->where('thread_id', $thread->id)->min('id');
                DB::table('support_notification_links')->insert([
                    'notification_id' => $notificationId,
                    'message_id' => $messageId,
                ]);
            }

            return DB::table('support_submission_reviews')->where('thread_id', $thread->id)->first();
        });

        return response()->json($result);
    }

    public function requestAccess(Request $request, SupportThread $thread): JsonResponse
    {
        $user = $request->user();
        abort_unless($this->submissionReview($thread)->status === 'approved', 404);
        abort_if($this->canAccess($thread, $user), 409, 'You already have access to this conversation.');
        $input = $request->validate(['message' => ['required', 'string', 'max:10000']]);

        $accessRequest = DB::transaction(function () use ($thread, $user, $input) {
            $existing = DB::table('support_access_requests')
                ->where('thread_id', $thread->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
            abort_if($existing?->status === 'pending', 409, 'Your access request is already pending.');
            abort_if($existing?->status === 'approved', 409, 'You already have access to this conversation.');

            if ($existing === null) {
                $id = DB::table('support_access_requests')->insertGetId([
                    'thread_id' => $thread->id,
                    'user_id' => $user->id,
                    'message' => $input['message'],
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $id = $existing->id;
                DB::table('support_access_requests')->where('id', $id)->update([
                    'message' => $input['message'],
                    'status' => 'pending',
                    'response_message' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'updated_at' => now(),
                ]);
            }

            $thread->touch();
            $recipients = User::where('role', User::ROLE_DEVELOPER)->whereKeyNot($user->id)->pluck('id');
            $this->notifyAccess($thread, $user, $recipients, $id, $user->username.' requested access');

            return DB::table('support_access_requests')->find($id);
        });

        return response()->json($accessRequest, 201);
    }

    public function reviewAccess(Request $request, SupportThread $thread, int $accessRequest): JsonResponse
    {
        abort_unless((int) $request->user()->role === User::ROLE_DEVELOPER, 403);
        $input = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'refused'])],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $result = DB::transaction(function () use ($request, $thread, $accessRequest, $input) {
            $item = DB::table('support_access_requests')
                ->where('id', $accessRequest)
                ->where('thread_id', $thread->id)
                ->lockForUpdate()
                ->first();
            abort_if($item === null, 404);
            abort_if($item->status !== 'pending', 409, 'This access request has already been reviewed.');

            DB::table('support_access_requests')->where('id', $item->id)->update([
                'status' => $input['decision'],
                'response_message' => $input['message'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);
            $thread->touch();
            $this->notifyAccess(
                $thread,
                $request->user(),
                collect([$item->user_id]),
                $item->id,
                'Access request '.$input['decision'],
            );

            return DB::table('support_access_requests')->find($item->id);
        });

        return response()->json($result);
    }

    private function authorizeThread(Request $request, SupportThread $thread): void
    {
        abort_unless($this->canAccess($thread, $request->user()), 404);
    }

    private function canAccess(SupportThread $thread, User $user): bool
    {
        if ((int) $user->role === User::ROLE_DEVELOPER || (int) $thread->user_id === (int) $user->id) {
            return true;
        }

        return DB::table('support_access_requests')
            ->where('thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();
    }

    private function submissionReview(SupportThread $thread): object
    {
        return DB::table('support_submission_reviews')->where('thread_id', $thread->id)->first()
            ?? (object) ['status' => 'approved', 'response_message' => null];
    }

    private function addMessage(SupportThread $thread, User $user, string $content, string $kind = 'reply'): SupportMessage
    {
        $message = new SupportMessage;
        $message->forceFill([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'kind' => $kind,
            'content' => $content,
        ])->save();
        $thread->touch();
        $this->markRead($thread, $user, $message->id);

        $participantIds = DB::table('support_access_requests')
            ->where('thread_id', $thread->id)
            ->where('status', 'approved')
            ->pluck('user_id');
        $recipients = User::where(function ($query) use ($thread, $participantIds) {
            $query->where('role', User::ROLE_DEVELOPER)
                ->orWhere('id', $thread->user_id)
                ->orWhereIn('id', $participantIds);
        })->where('id', '!=', $user->id)->pluck('id');

        foreach ($recipients as $recipient) {
            $id = $this->createNotification($user, $recipient, 'Support #'.$thread->id.': '.$thread->subject);
            DB::table('support_notification_links')->insert([
                'notification_id' => $id,
                'message_id' => $message->id,
            ]);
        }

        return $message;
    }

    private function notifyAccess(SupportThread $thread, User $sender, $recipients, int $accessRequestId, string $action): void
    {
        foreach ($recipients as $recipient) {
            $id = $this->createNotification($sender, $recipient, $action.' for Support #'.$thread->id.': '.$thread->subject);
            DB::table('support_access_notification_links')->insert([
                'notification_id' => $id,
                'access_request_id' => $accessRequestId,
            ]);
        }
    }

    private function createNotification(User $sender, int $recipient, string $content): int
    {
        return DB::table('notifications')->insertGetId([
            'from_id' => $sender->id,
            'to_id' => $recipient,
            'content' => $content,
            'seen' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function markRead(SupportThread $thread, User $user, int $messageId): void
    {
        $key = ['thread_id' => $thread->id, 'user_id' => $user->id];
        $previous = DB::table('support_reads')->where($key)->value('last_read_message_id');
        DB::table('support_reads')->updateOrInsert($key, [
            'last_read_message_id' => max((int) $previous, $messageId),
        ]);
        $messageNotificationIds = DB::table('support_notification_links')
            ->join('support_messages', 'support_messages.id', '=', 'support_notification_links.message_id')
            ->where('support_messages.thread_id', $thread->id)
            ->where('support_messages.id', '<=', $messageId)
            ->select('notification_id');
        $accessNotificationIds = DB::table('support_access_notification_links')
            ->join('support_access_requests', 'support_access_requests.id', '=', 'support_access_notification_links.access_request_id')
            ->where('support_access_requests.thread_id', $thread->id)
            ->select('notification_id');
        DB::table('notifications')->where('to_id', $user->id)
            ->where(function ($query) use ($messageNotificationIds, $accessNotificationIds) {
                $query->whereIn('id', $messageNotificationIds)->orWhereIn('id', $accessNotificationIds);
            })->update(['seen' => 1]);
    }
}
