# Contact module

Owns the public contact submission boundary. Messages are validated, throttled and queued directly
to the privately configured recipient; they are not stored in an application contact-message
table. The honeypot field creates no accessibility challenge and bot submissions receive the same
generic response without queueing mail.

The recipient is resolved through the Settings contract and never exposed merely to enable the
form. Queued payloads are encrypted, retries are bounded, and failed queue records are pruned
daily after seven days. Production mail credentials remain environment configuration.
