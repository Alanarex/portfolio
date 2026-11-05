<?php

declare(strict_types=1);

namespace Modules\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Posts\Enums\PostStatus;
use Modules\Posts\Http\Requests\SavePostRequest;
use Modules\Posts\Models\Post;

final class PostsController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Post::class);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => Post::query()->latest('id')->limit(200)->get()->map(fn (Post $post): array => [
                'id' => $post->id,
                'locale' => $post->locale,
                'body' => $post->body,
                'link_url' => $post->link_url ?? '',
                'link_label' => $post->link_label ?? '',
                'status' => $post->status->value,
                'published_at' => $post->published_at?->toIso8601String(),
            ])->all(),
        ]);
    }

    public function store(SavePostRequest $request): RedirectResponse
    {
        Gate::authorize('create', Post::class);
        $post = new Post;
        $this->fill($post, $request);
        $post->save();

        return back()->with('success', 'Publication enregistrée.');
    }

    public function update(SavePostRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);
        $this->fill($post, $request);
        $post->save();

        return back()->with('success', 'Publication mise à jour.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return back()->with('success', 'Publication supprimée.');
    }

    private function fill(Post $post, SavePostRequest $request): void
    {
        /** @var array{locale: string, body: string, link_url?: string|null, link_label?: string|null, status: string} $data */
        $data = $request->validated();
        $status = PostStatus::from($data['status']);

        $post->fill([
            'locale' => $data['locale'],
            'body' => trim($data['body']),
            'link_url' => ($data['link_url'] ?? '') !== '' ? $data['link_url'] : null,
            'link_label' => ($data['link_label'] ?? '') !== '' ? $data['link_label'] : null,
            'status' => $status,
        ]);

        // The first publication keeps its date; going back to draft clears it.
        if ($status === PostStatus::Published) {
            $post->published_at ??= now();
        } else {
            $post->published_at = null;
        }
    }
}
