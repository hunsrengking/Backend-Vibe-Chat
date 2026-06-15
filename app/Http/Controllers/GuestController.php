<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterGuestRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\GuestResource;
use App\Models\Guest;
use App\Repositories\GuestRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GuestController extends Controller
{
    protected GuestRepositoryInterface $guestRepository;

    public function __construct(GuestRepositoryInterface $guestRepository)
    {
        $this->guestRepository = $guestRepository;
    }

    /**
     * Create a new guest account with username and password.
     */
    public function store(RegisterGuestRequest $request)
    {
        $validated = $request->validated();
        $uuid = (string) Str::uuid();

        $guest = $this->guestRepository->create([
            'username' => $validated['username'],
            'password' => $validated['password'],
            'guest_token' => $uuid,
            'nickname' => $validated['nickname'],
            'avatar_url' => $validated['avatar_url'] ?? null,
            'is_admin' => false,
        ]);

        $token = $guest->createToken('guest-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'guest' => new GuestResource($guest),
        ], 201);
    }

    /**
     * Authenticate a guest using username and password.
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $guest = $this->guestRepository->findByUsername($validated['username']);

        if (!$guest || !Hash::check($validated['password'], $guest->password)) {
            return response()->json([
                'error' => 'Invalid username or password.'
            ], 401);
        }

        $token = $guest->createToken('guest-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'guest' => new GuestResource($guest),
        ]);
    }

    /**
     * Get the authenticated guest's details.
     */
    public function show(Request $request)
    {
        return new GuestResource($request->user());
    }

    /**
     * Update the authenticated guest's profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        $guest = $request->user();
        $guest->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully.',
            'guest' => new GuestResource($guest),
        ]);
    }

    /**
     * Fetch public profile statistics for a guest.
     */
    public function getPublicProfile($id)
    {
        $guest = Guest::withCount('posts')->findOrFail((int)$id);
        return response()->json([
            'data' => [
                'id' => $guest->id,
                'nickname' => $guest->nickname,
                'avatar_url' => $guest->avatar_url,
                'created_at' => $guest->created_at->toIso8601String(),
                'posts_count' => $guest->posts_count,
            ]
        ]);
    }

    /**
     * Fetch paginated posts of a given guest.
     */
    public function getPublicPosts(Request $request, $id)
    {
        $perPage = $request->query('per_page', 10);
        $posts = \App\Models\Post::with(['guest', 'likes'])
            ->withCount('comments')
            ->where('guest_id', (int)$id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return \App\Http\Resources\PostResource::collection($posts);
    }
}
