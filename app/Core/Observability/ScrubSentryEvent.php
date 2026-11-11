<?php

declare(strict_types=1);

namespace App\Core\Observability;

use Sentry\Event;
use Sentry\EventHint;

/**
 * Last line of defence before an error report leaves the server.
 *
 * Only the error itself, the stack trace, the HTTP method and the URL path are kept. Request bodies
 * (contact form), query strings, cookies, headers, visitor identity and breadcrumbs (which can echo
 * log lines or SQL) are dropped. Registered as a static callable so `config:cache` keeps working.
 */
final class ScrubSentryEvent
{
    public static function beforeSend(Event $event, ?EventHint $hint = null): Event
    {
        $request = $event->getRequest();
        $url = isset($request['url']) && is_string($request['url']) ? strtok($request['url'], '?') : null;

        $event->setRequest(array_filter([
            'method' => $request['method'] ?? null,
            'url' => $url === false ? null : $url,
        ], static fn (mixed $value): bool => $value !== null));
        $event->setUser(null);
        $event->setBreadcrumb([]);
        $event->setExtra([]);
        $event->setServerName(null);

        return $event;
    }
}
