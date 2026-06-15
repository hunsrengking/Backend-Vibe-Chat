<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// Public Endpoints
Route::get('/health', function () {
    return response()->json(['status' => 'alive', 'timestamp' => now()]);
});
Route::post('/guest', [GuestController::class, 'store']);
Route::post('/login', [GuestController::class, 'login']);

// Deployment helper routes (accessible via /api/deploy/{action})
Route::get('/deploy/{action}', function ($action) {
    if (request('key') !== 'deploy123') {
        return response('Unauthorized: Invalid key parameter. Use ?key=deploy123', 403);
    }

    try {
        switch ($action) {
            case 'migrate':
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                break;
            case 'storage-link':
                \Illuminate\Support\Facades\Artisan::call('storage:link');
                break;
            case 'cache':
                \Illuminate\Support\Facades\Artisan::call('config:cache');
                \Illuminate\Support\Facades\Artisan::call('route:cache');
                \Illuminate\Support\Facades\Artisan::call('view:cache');
                break;
            case 'clear':
                \Illuminate\Support\Facades\Artisan::call('optimize:clear');
                break;
            default:
                return "Unknown action. Available actions: migrate, storage-link, cache, clear";
        }
        return "Action '$action' executed successfully:\n\n" . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        return "Error executing '$action':\n\n" . $e->getMessage();
    }
});

// Authenticated Endpoints (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Guest Profile
    Route::get('/guest', [GuestController::class, 'show']);
    Route::put('/guest', [GuestController::class, 'update']);

    // Posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/trending', [PostController::class, 'trending']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::post('/posts/{post}/like', [PostController::class, 'toggleLike']);

    // Comments
    Route::get('/comments/{postId}', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Chat room messages
    Route::get('/messages', [ChatMessageController::class, 'index']);
    Route::post('/messages', [ChatMessageController::class, 'store']);

    // Group Chats
    Route::get('/groups', [\App\Http\Controllers\GroupChatController::class, 'index']);
    Route::post('/groups', [\App\Http\Controllers\GroupChatController::class, 'store']);
    Route::post('/groups/{groupId}/join', [\App\Http\Controllers\GroupChatController::class, 'join']);
    Route::get('/groups/{group}/requests', [\App\Http\Controllers\GroupChatController::class, 'getRequests']);
    Route::post('/groups/{group}/requests/{request}/handle', [\App\Http\Controllers\GroupChatController::class, 'handleRequest']);

    // Direct Messages
    Route::get('/direct-messages/conversations', [\App\Http\Controllers\DirectMessageController::class, 'conversations']);
    Route::get('/direct-messages/{receiverId}', [\App\Http\Controllers\DirectMessageController::class, 'index']);
    Route::post('/direct-messages', [\App\Http\Controllers\DirectMessageController::class, 'store']);

    // Public Profiles
    Route::get('/guests/{id}', [\App\Http\Controllers\GuestController::class, 'getPublicProfile']);
    Route::get('/guests/{id}/posts', [\App\Http\Controllers\GuestController::class, 'getPublicPosts']);

    // Admin Promotion & Dashboard
    Route::post('/admin/auth', [AdminController::class, 'auth']);
    Route::get('/admin/stats', [AdminController::class, 'stats']);
    Route::get('/admin/posts', [AdminController::class, 'posts']);
    Route::get('/admin/comments', [AdminController::class, 'comments']);
});
