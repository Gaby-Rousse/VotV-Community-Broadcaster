<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    expect(DB::connection()->getDriverName())->toBe('sqlite');
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('username');
        $table->string('password');
        $table->integer('role')->default(0);
        $table->timestamps();
    });
    Schema::create('notifications', function (Blueprint $table) {
        $table->increments('id');
        $table->foreignId('from_id')->constrained('users');
        $table->foreignId('to_id')->constrained('users');
        $table->string('content', 1000);
        $table->boolean('seen')->default(false);
        $table->timestamps();
    });
    Schema::create('support_threads', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained();
        $table->enum('type', ['suggestion', 'bug']);
        $table->string('subject', 200);
        $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
        $table->timestamps();
    });
    Schema::create('support_messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('thread_id')->constrained('support_threads');
        $table->foreignId('user_id')->constrained();
        $table->enum('kind', ['reply', 'status'])->default('reply');
        $table->text('content');
        $table->timestamps();
    });
    Schema::create('support_submission_reviews', function (Blueprint $table) {
        $table->foreignId('thread_id')->primary()->constrained('support_threads');
        $table->enum('status', ['pending', 'approved', 'refused'])->default('pending');
        $table->text('response_message')->nullable();
        $table->foreignId('reviewed_by')->nullable()->constrained('users');
        $table->timestamp('reviewed_at')->nullable();
        $table->timestamps();
    });
    Schema::create('support_access_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('thread_id')->constrained('support_threads');
        $table->foreignId('user_id')->constrained('users');
        $table->text('message');
        $table->enum('status', ['pending', 'approved', 'refused'])->default('pending');
        $table->text('response_message')->nullable();
        $table->foreignId('reviewed_by')->nullable()->constrained('users');
        $table->timestamp('reviewed_at')->nullable();
        $table->timestamps();
        $table->unique(['thread_id', 'user_id']);
    });
    Schema::create('support_reads', function (Blueprint $table) {
        $table->foreignId('thread_id')->constrained('support_threads');
        $table->foreignId('user_id')->constrained();
        $table->foreignId('last_read_message_id')->constrained('support_messages');
        $table->primary(['thread_id', 'user_id']);
    });
    Schema::create('support_notification_links', function (Blueprint $table) {
        $table->unsignedInteger('notification_id')->primary();
        $table->foreign('notification_id')->references('id')->on('notifications');
        $table->foreignId('message_id')->constrained('support_messages');
    });
    Schema::create('support_access_notification_links', function (Blueprint $table) {
        $table->unsignedInteger('notification_id')->primary();
        $table->foreign('notification_id')->references('id')->on('notifications');
        $table->foreignId('access_request_id')->constrained('support_access_requests');
    });

    $this->owner = User::forceCreate(['username' => 'owner', 'role' => 0, 'password' => 'password']);
    $this->outsider = User::forceCreate(['username' => 'outsider', 'role' => 0, 'password' => 'password']);
    $this->developer = User::forceCreate(['username' => 'developer', 'role' => User::ROLE_DEVELOPER, 'password' => 'password']);
});

function submitSupportThread($test, string $type = 'bug', bool $approve = true): int
{
    Sanctum::actingAs($test->owner);

    $threadId = $test->postJson('/api/v1/support/threads', [
        'subject' => 'Playback stops', 'type' => $type, 'content' => 'The stream stops after a few seconds.',
    ])->assertCreated()->json('id');

    if ($approve) {
        DB::table('support_submission_reviews')->where('thread_id', $threadId)->update([
            'status' => 'approved',
            'response_message' => 'Approved for testing.',
            'reviewed_by' => $test->developer->id,
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $threadId;
}

test('creating a submission stores a pending review and notifies developers', function () {
    $id = submitSupportThread($this, 'bug', false);

    $this->assertDatabaseHas('support_threads', ['id' => $id, 'user_id' => $this->owner->id, 'status' => 'open']);
    $this->assertDatabaseHas('support_messages', ['thread_id' => $id, 'content' => 'The stream stops after a few seconds.']);
    $this->assertDatabaseHas('support_submission_reviews', ['thread_id' => $id, 'status' => 'pending']);
    $this->assertDatabaseHas('notifications', ['from_id' => $this->owner->id, 'to_id' => $this->developer->id, 'seen' => 0]);
    $this->assertDatabaseCount('notifications', 1);
    $this->getJson('/api/v1/support/threads')->assertOk()->assertJsonPath('data.0.unread', false);

    Sanctum::actingAs($this->developer);
    $this->getJson('/api/v1/support/threads')->assertOk()
        ->assertJsonPath('data.0.unread', false)
        ->assertJsonPath('data.0.approval_status', 'pending');
});

test('users can read but cannot participate in another users submission', function () {
    $id = submitSupportThread($this);
    Sanctum::actingAs($this->outsider);

    $this->getJson('/api/v1/support/threads')->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.can_reply', false);
    $this->getJson('/api/v1/support/threads/'.$id)->assertOk()
        ->assertJsonPath('messages.0.content', 'The stream stops after a few seconds.')
        ->assertJsonPath('can_reply', false);
    $this->postJson('/api/v1/support/threads/'.$id.'/messages', ['content' => 'intrusion'])->assertNotFound();
    $this->patchJson('/api/v1/support/threads/'.$id, ['status' => 'closed'])->assertNotFound();
    $this->postJson('/api/v1/support/threads/'.$id.'/read', ['message_id' => 1])->assertNotFound();
});

test('only developers can change status and closed threads reject replies', function () {
    $id = submitSupportThread($this);
    $this->patchJson('/api/v1/support/threads/'.$id, ['status' => 'closed'])->assertForbidden();

    Sanctum::actingAs($this->developer);
    $this->patchJson('/api/v1/support/threads/'.$id, ['status' => 'closed'])->assertOk();
    $this->assertDatabaseHas('support_messages', ['thread_id' => $id, 'kind' => 'status', 'content' => 'Status changed to closed.']);
    $this->assertDatabaseHas('notifications', ['to_id' => $this->owner->id, 'seen' => 0]);
    $this->postJson('/api/v1/support/threads/'.$id.'/messages', ['content' => 'reply'])->assertStatus(409);
    $this->patchJson('/api/v1/support/threads/'.$id, ['status' => 'open'])->assertOk();
    $this->postJson('/api/v1/support/threads/'.$id.'/messages', ['content' => 'Please try again.'])->assertCreated();
});

test('reading a thread clears only that users notifications up to the viewed message', function () {
    $id = submitSupportThread($this);
    Sanctum::actingAs($this->developer);
    $first = $this->postJson('/api/v1/support/threads/'.$id.'/messages', ['content' => 'First reply'])->assertCreated()->json('id');
    $second = $this->postJson('/api/v1/support/threads/'.$id.'/messages', ['content' => 'Second reply'])->assertCreated()->json('id');

    Sanctum::actingAs($this->owner);
    $this->getJson('/api/v1/support/threads')->assertJsonPath('data.0.unread', true);
    $this->postJson('/api/v1/support/threads/'.$id.'/read', ['message_id' => $first])->assertOk();
    expect(DB::table('notifications')->where('to_id', $this->owner->id)->where('seen', 0)->count())->toBe(1);
    $this->getJson('/api/v1/support/threads')->assertJsonPath('data.0.unread', true);
    $this->postJson('/api/v1/support/threads/'.$id.'/read', ['message_id' => $second])->assertOk();
    $this->postJson('/api/v1/support/threads/'.$id.'/read', ['message_id' => $first])->assertOk();
    $this->getJson('/api/v1/support/threads')->assertJsonPath('data.0.unread', false);
    expect(DB::table('notifications')->where('to_id', $this->owner->id)->where('seen', 0)->count())->toBe(0);
});

test('guest requests are rejected', function () {
    submitSupportThread($this);
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/v1/support/threads?type=bug')->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.can_reply', false);
    $this->getJson('/api/v1/support/threads/1')->assertOk()
        ->assertJsonPath('messages.0.content', 'The stream stops after a few seconds.')
        ->assertJsonPath('can_reply', false);
    $this->postJson('/api/v1/support/threads', ['subject' => 'test'])->assertUnauthorized();
});

test('invalid submissions do not create threads and owners cannot be spoofed', function () {
    Sanctum::actingAs($this->owner);
    $this->postJson('/api/v1/support/threads', ['subject' => ' ', 'type' => 'other', 'content' => ' '])
        ->assertUnprocessable()->assertJsonValidationErrors(['subject', 'type', 'content']);
    $this->assertDatabaseCount('support_threads', 0);
    $this->postJson('/api/v1/support/threads', [
        'subject' => 'Hello', 'type' => 'suggestion', 'content' => 'A suggestion', 'user_id' => $this->developer->id, 'status' => 'resolved',
    ])->assertCreated()->assertJsonPath('user_id', $this->owner->id)->assertJsonPath('status', 'open');
});

test('thread lists are public while mine remains limited to the current user', function () {
    submitSupportThread($this, 'suggestion');
    submitSupportThread($this, 'bug');
    Sanctum::actingAs($this->developer);
    $this->getJson('/api/v1/support/threads?type=bug')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.type', 'bug');
    $this->getJson('/api/v1/support/threads?mine=1')->assertOk()->assertJsonCount(0, 'data');
});

test('messages are paginated chronologically and users cannot mark a different thread as read', function () {
    $id = submitSupportThread($this);
    $other = submitSupportThread($this);
    $otherMessage = DB::table('support_messages')->where('thread_id', $other)->value('id');
    $this->postJson('/api/v1/support/threads/'.$id.'/read', ['message_id' => $otherMessage])->assertUnprocessable();

    for ($i = 0; $i < 55; $i++) {
        DB::table('support_messages')->insert(['thread_id' => $id, 'user_id' => $this->owner->id, 'kind' => 'reply', 'content' => 'Reply '.$i, 'created_at' => now(), 'updated_at' => now()]);
    }
    $recent = $this->getJson('/api/v1/support/threads/'.$id)->assertOk()->assertJsonCount(50, 'messages')->assertJsonPath('has_older', true)->json('messages');
    expect($recent[0]['id'])->toBeLessThan($recent[49]['id']);
    $this->getJson('/api/v1/support/threads/'.$id.'?before='.$recent[0]['id'])->assertOk()->assertJsonCount(6, 'messages')->assertJsonPath('has_older', false);
});

test('notification center lists unread support notifications with their destination', function () {
    $threadId = submitSupportThread($this, 'bug');
    Sanctum::actingAs($this->developer);

    $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('notifications.total', 1)
        ->assertJsonPath('notifications.data.0.seen', false)
        ->assertJsonPath('notifications.data.0.from.username', 'owner')
        ->assertJsonPath('notifications.data.0.url', '/bugs?thread='.$threadId);
});

test('only the recipient can mark a notification as read', function () {
    $threadId = submitSupportThread($this, 'suggestion');
    $notificationId = DB::table('notifications')->value('id');

    Sanctum::actingAs($this->outsider);
    $this->postJson('/api/v1/notifications/'.$notificationId.'/read')->assertNotFound();
    $this->assertDatabaseHas('notifications', ['id' => $notificationId, 'seen' => 0]);

    Sanctum::actingAs($this->developer);
    $this->postJson('/api/v1/notifications/'.$notificationId.'/read')
        ->assertOk()
        ->assertJsonPath('read', true)
        ->assertJsonPath('url', '/suggestions?thread='.$threadId);
    $this->assertDatabaseHas('notifications', ['id' => $notificationId, 'seen' => 1]);
    $this->getJson('/api/v1/notifications')->assertOk()->assertJsonPath('unread_count', 0);
});

test('notification center requires authentication', function () {
    $this->getJson('/api/v1/notifications')->assertUnauthorized();
    $this->postJson('/api/v1/notifications/1/read')->assertUnauthorized();
});

test('users request access and developers approve them with messages', function () {
    $threadId = submitSupportThread($this, 'suggestion');
    Sanctum::actingAs($this->outsider);

    $this->postJson('/api/v1/support/threads/'.$threadId.'/access-requests', [
        'message' => 'I can help reproduce and document this idea.',
    ])->assertCreated()->assertJsonPath('status', 'pending');
    $this->assertDatabaseHas('support_access_requests', [
        'thread_id' => $threadId,
        'user_id' => $this->outsider->id,
        'status' => 'pending',
    ]);
    $this->getJson('/api/v1/support/threads/'.$threadId)->assertOk()
        ->assertJsonPath('can_reply', false);

    Sanctum::actingAs($this->developer);
    $notification = $this->getJson('/api/v1/notifications')->assertOk();
    $notification->assertJsonPath('notifications.data.0.url', '/suggestions?thread='.$threadId);
    $requests = $this->getJson('/api/v1/support/threads/requests/pending')->assertOk();
    $requests->assertJsonPath('access_requests.0.user.username', 'outsider')
        ->assertJsonPath('access_requests.0.message', 'I can help reproduce and document this idea.');
    $requestId = $requests->json('access_requests.0.id');
    $this->patchJson('/api/v1/support/threads/'.$threadId.'/access-requests/'.$requestId, [
        'decision' => 'approved',
    ])->assertUnprocessable()->assertJsonValidationErrors('message');
    $this->patchJson('/api/v1/support/threads/'.$threadId.'/access-requests/'.$requestId, [
        'decision' => 'approved',
        'message' => 'Approved. Please add anything useful to the conversation.',
    ])->assertOk()->assertJsonPath('status', 'approved');

    Sanctum::actingAs($this->outsider);
    $this->getJson('/api/v1/notifications')->assertOk()
        ->assertJsonPath('notifications.data.0.url', '/suggestions?thread='.$threadId);
    $this->getJson('/api/v1/support/threads?type=suggestion')->assertOk()
        ->assertJsonPath('data.0.can_reply', true)
        ->assertJsonPath('data.0.access_request.response_message', 'Approved. Please add anything useful to the conversation.');
    $this->getJson('/api/v1/support/threads/'.$threadId)->assertOk();
    $this->postJson('/api/v1/support/threads/'.$threadId.'/messages', [
        'content' => 'I reproduced this and have more details.',
    ])->assertCreated();
    $this->assertDatabaseHas('notifications', ['from_id' => $this->outsider->id, 'to_id' => $this->owner->id]);
    $this->assertDatabaseHas('notifications', ['from_id' => $this->outsider->id, 'to_id' => $this->developer->id]);
});

test('refused users see the response and can submit a new request', function () {
    $threadId = submitSupportThread($this);
    Sanctum::actingAs($this->outsider);
    $requestId = $this->postJson('/api/v1/support/threads/'.$threadId.'/access-requests', [
        'message' => 'Please let me join.',
    ])->assertCreated()->json('id');

    Sanctum::actingAs($this->developer);
    $this->patchJson('/api/v1/support/threads/'.$threadId.'/access-requests/'.$requestId, [
        'decision' => 'refused',
        'message' => 'There is not enough information in your request.',
    ])->assertOk();

    Sanctum::actingAs($this->outsider);
    $this->getJson('/api/v1/support/threads?type=bug')->assertOk()
        ->assertJsonPath('data.0.can_reply', false)
        ->assertJsonPath('data.0.access_request.status', 'refused')
        ->assertJsonPath('data.0.access_request.response_message', 'There is not enough information in your request.');
    $this->postJson('/api/v1/support/threads/'.$threadId.'/access-requests', [
        'message' => 'Here is the additional information you requested.',
    ])->assertCreated()->assertJsonPath('status', 'pending');
});

test('only developers review access requests and existing members cannot request access', function () {
    $threadId = submitSupportThread($this);
    Sanctum::actingAs($this->outsider);
    $requestId = $this->postJson('/api/v1/support/threads/'.$threadId.'/access-requests', [
        'message' => 'Please let me join.',
    ])->assertCreated()->json('id');
    $this->patchJson('/api/v1/support/threads/'.$threadId.'/access-requests/'.$requestId, [
        'decision' => 'approved',
        'message' => 'Trying to approve myself.',
    ])->assertForbidden();

    Sanctum::actingAs($this->owner);
    $this->postJson('/api/v1/support/threads/'.$threadId.'/access-requests', [
        'message' => 'I already own this.',
    ])->assertStatus(409);
});

test('pending submissions stay private until a developer approves them with a message', function () {
    $threadId = submitSupportThread($this, 'suggestion', false);

    Sanctum::actingAs($this->outsider);
    $this->getJson('/api/v1/support/threads?type=suggestion')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/support/threads/'.$threadId)->assertNotFound();

    Sanctum::actingAs($this->developer);
    $this->getJson('/api/v1/support/threads/requests/pending')->assertOk()
        ->assertJsonPath('submissions.0.id', $threadId)
        ->assertJsonPath('submissions.0.author.username', 'owner');
    $this->patchJson('/api/v1/support/threads/'.$threadId.'/submission-review', [
        'decision' => 'approved',
    ])->assertUnprocessable()->assertJsonValidationErrors('message');
    $this->patchJson('/api/v1/support/threads/'.$threadId.'/submission-review', [
        'decision' => 'approved',
        'message' => 'Approved for the public suggestions list.',
    ])->assertOk()->assertJsonPath('status', 'approved');

    Sanctum::actingAs($this->outsider);
    $this->getJson('/api/v1/support/threads?type=suggestion')->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.approval_status', 'approved');
    $this->getJson('/api/v1/support/threads/'.$threadId)->assertOk();

    Sanctum::actingAs($this->owner);
    $this->getJson('/api/v1/notifications')->assertOk()
        ->assertJsonPath('notifications.data.0.url', '/suggestions?thread='.$threadId);
});

test('refused submissions remain visible to their author with the developers response', function () {
    $threadId = submitSupportThread($this, 'bug', false);
    Sanctum::actingAs($this->developer);
    $this->patchJson('/api/v1/support/threads/'.$threadId.'/submission-review', [
        'decision' => 'refused',
        'message' => 'Please provide reproducible steps before submitting again.',
    ])->assertOk();

    Sanctum::actingAs($this->owner);
    $this->getJson('/api/v1/support/threads?mine=1')->assertOk()
        ->assertJsonPath('data.0.approval_status', 'refused')
        ->assertJsonPath('data.0.approval_message', 'Please provide reproducible steps before submitting again.')
        ->assertJsonPath('data.0.can_reply', false);

    Sanctum::actingAs($this->outsider);
    $this->getJson('/api/v1/support/threads?type=bug')->assertOk()->assertJsonCount(0, 'data');
});

test('developer request queue includes pending conversation access requests', function () {
    $threadId = submitSupportThread($this);
    Sanctum::actingAs($this->outsider);
    $requestId = $this->postJson('/api/v1/support/threads/'.$threadId.'/access-requests', [
        'message' => 'I would like to help investigate.',
    ])->assertCreated()->json('id');

    Sanctum::actingAs($this->developer);
    $this->getJson('/api/v1/support/threads/requests/pending')->assertOk()
        ->assertJsonPath('access_requests.0.id', $requestId)
        ->assertJsonPath('access_requests.0.thread.id', $threadId)
        ->assertJsonPath('access_requests.0.user.username', 'outsider');
});
