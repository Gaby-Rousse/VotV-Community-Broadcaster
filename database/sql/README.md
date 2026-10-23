# Support inbox

Run `support.sql` against the VCB MariaDB database before opening `/support`.
It creates seven new tables and leaves the existing users, notifications, bugs,
suggestions, and media records intact. It expects the current `users.id`
(unsigned bigint) and `notifications.id` (signed int) definitions.

The application stores conversations in `support_threads` and replies or status
changes in `support_messages`. `support_reads` tracks each user's last viewed
message. `support_notification_links` associates existing in-site notifications
with those messages. No email is sent by the support feature.

Everyone can browse suggestions and bugs and read their conversations. Only the
author, users with the Developer role (`3`), and users whose access request a
developer approved can reply. Access requests and decisions require messages
and use the existing local notification system. Developers can also change
submission status. Closed threads keep their history and reject replies until a
developer reopens them.

New submissions begin as pending requests. Only their author and developers can
see them until a developer approves them. Approval and refusal both require a
response message and notify the author. Existing submissions without a review
record are treated as approved.

This initial implementation does not import the legacy single-message bugs and
suggestions into conversations. That requires a separate data migration.

The UI refreshes every 15 seconds, lists 25 threads per page, and loads messages
in batches of 50. Run `php artisan test --compact` for the isolated tests and
`npx tsc --noEmit` plus `npm run build` for frontend checks.
