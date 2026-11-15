<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\ActivityLog\Models\AuditEvent;
use Modules\Career\Models\Experience;
use Modules\Posts\Enums\PostStatus;
use Modules\Posts\Models\Post;
use Modules\Projects\Enums\PublicationStatus;
use Modules\Projects\Models\Project;
use Modules\Skills\Models\Skill;

/** Page d'accueil du dashboard : synthèse en lecture seule, sans donnée personnelle. */
final class DashboardHomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard/Home', [
            'stats' => [
                'projects' => [
                    'published' => Project::query()->where('publication_status', PublicationStatus::Published)->count(),
                    'draft' => Project::query()->where('publication_status', PublicationStatus::Draft)->count(),
                ],
                'posts' => [
                    'published' => Post::query()->where('status', PostStatus::Published)->count(),
                    'draft' => Post::query()->where('status', PostStatus::Draft)->count(),
                ],
                'experiences' => [
                    'published' => Experience::query()->where('status', PublicationStatus::Published)->count(),
                    'draft' => Experience::query()->where('status', '!=', PublicationStatus::Published)->count(),
                ],
                'skills' => [
                    'published' => Skill::query()->where('status', PublicationStatus::Published)->count(),
                    'draft' => Skill::query()->where('status', '!=', PublicationStatus::Published)->count(),
                ],
            ],
            'activity' => AuditEvent::query()->latest('id')->limit(6)->get()->map(fn (AuditEvent $event): array => [
                'id' => $event->id,
                'action' => $event->action,
                'subject' => class_basename($event->subject_type),
                'fields' => count($event->changed_fields),
                'created_at' => $event->getAttribute('created_at')?->toIso8601String(),
            ])->all(),
        ]);
    }
}
