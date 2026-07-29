<?php

declare(strict_types=1);

namespace Modules\Contact\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Modules\Contact\Data\ContactSubmissionData;
use Modules\Contact\Http\Requests\SubmitContactRequest;
use Modules\Contact\Mail\ContactMessageMail;
use Modules\Settings\Contracts\ContactRecipientReader;

final class ContactController extends Controller
{
    public function __invoke(
        SubmitContactRequest $request,
        string $locale,
        ContactRecipientReader $recipients,
    ): RedirectResponse {
        abort_unless(in_array($locale, ['fr', 'en'], true), 404);
        app()->setLocale($locale);

        $recipient = $recipients->recipient();
        abort_if($recipient === null, 404);

        if (filled($request->validated('website'))) {
            return back()->with('contact_success', __('portfolio.contact.success'));
        }

        Mail::to($recipient)->queue(new ContactMessageMail(new ContactSubmissionData(
            name: trim((string) $request->validated('name')),
            email: mb_strtolower(trim((string) $request->validated('email'))),
            subject: filled($request->validated('subject'))
                ? trim((string) $request->validated('subject'))
                : null,
            message: trim((string) $request->validated('message')),
            locale: $locale,
        )));

        return back()->with('contact_success', __('portfolio.contact.success'));
    }
}
