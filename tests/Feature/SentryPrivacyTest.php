<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Observability\ScrubSentryEvent;
use Sentry\Event;
use Sentry\UserDataBag;
use Tests\TestCase;

final class SentryPrivacyTest extends TestCase
{
    public function test_sentry_is_disabled_without_a_dsn_and_never_sends_default_pii(): void
    {
        self::assertEmpty(config('sentry.dsn'));
        self::assertFalse(config('sentry.send_default_pii'));
        self::assertNull(config('sentry.traces_sample_rate'));
        self::assertSame([ScrubSentryEvent::class, 'beforeSend'], config('sentry.before_send'));
    }

    public function test_scrubber_keeps_only_method_and_url_path_and_drops_visitor_data(): void
    {
        $event = Event::createEvent();
        $event->setRequest([
            'method' => 'POST',
            'url' => 'https://alaakhalil.dev/fr/contact?token=secret&email=a@b.test',
            'query_string' => 'token=secret',
            'data' => ['name' => 'Camille', 'email' => 'camille@example.test', 'message' => 'Bonjour'],
            'cookies' => ['portfolio_session' => 'abc'],
            'headers' => ['Authorization' => ['Bearer abc'], 'Cookie' => ['x=y']],
        ]);
        $event->setUser(UserDataBag::createFromUserIpAddress('203.0.113.7'));
        $event->setBreadcrumb([]);
        $event->setExtra(['payload' => 'sensitive']);
        $event->setServerName('private-host');

        $scrubbed = ScrubSentryEvent::beforeSend($event);

        self::assertSame(
            ['method' => 'POST', 'url' => 'https://alaakhalil.dev/fr/contact'],
            $scrubbed->getRequest(),
        );
        self::assertNull($scrubbed->getUser());
        self::assertSame([], $scrubbed->getExtra());
        self::assertNull($scrubbed->getServerName());
    }
}
