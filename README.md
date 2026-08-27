# Seeker

Seeker is an Azuriom talent portal where community members can offer freelance commissions or look for collaborators for their projects.

## Requirements

- Azuriom 1.2.x (extension API 1.2.0)
- PHP 8.2 or newer

## Current features

- Public catalog with text search and publication-type filters.
- Authenticated creation and management of commission offers and talent searches.
- Account and IP rate limits plus Azuriom-configured CAPTCHA verification for publication creation and editing.
- Administrative settings for feature availability and fully configurable publication rate-limit attempts and windows.
- Global Seeker availability switch that blocks every public route and action while keeping the administrative panel available for recovery.
- Consistent Zibuu-style administrative experience with contextual headers, responsive filter toolbars, accessible compact actions, polished tables, statistics, and empty states.
- Publication moderation with creation and edit timestamps, linked conversation counts, and a complete administrative detail view.
- Read-only conversation moderation with participant, publication, report, payment, and message context plus force-close controls.
- Unified report moderation for publications, profiles, and conversations, including evidence snapshots and direct target links.
- Individual timed or indefinite restrictions for publishing, starting contacts, profile visibility, and complete Seeker access, with moderator attribution and revocation history.
- Moderation-safe bulk publication removal that preserves linked conversations, reports, and financial evidence.
- Administrative point ledger with payer, recipient, publication, hold and delivery state, service and tip breakdowns, filters, and aggregate Seeker spending statistics.
- One required portfolio format per publication: an HTTP/HTTPS external link, uploaded reference images, one video, or one audio file; formats cannot be combined.
- Between one and six privately stored images when the uploaded portfolio format is selected (JPG, PNG, or WebP, 5 MB each).
- Same-page reference gallery with keyboard navigation, image counters, and responsive previews.
- One privately stored MP4 or WebM video, or one MP3, WAV, OGG, or M4A audio file, with a 10 MB limit and server-side MIME verification.
- Per-publication visibility for everyone or authenticated Zibuu members only.
- Informational service pricing as fixed or hourly Zibuu points, free, or to be agreed between interested users.
- Private one-to-one conversations between publication authors and interested members.
- Participant-only conversation images, limited to one privately stored JPG, PNG, or WebP attachment per message.
- Transactional point holds for fixed-price services, with duplicate-contact protection and no early delivery to the author.
- Author-requested completion for fixed and hourly point commissions, with persistent delivery-attempt counts, client approval, optional tips, final messages, and read-only completed conversations.
- Mutual verified ratings and short experience comments after completion, aggregated as user reputation across every publication.
- Public Seeker profiles with biographies, role-specific reputation, verified reviews, commission statistics, and active publications.
- Private profile reporting with biography snapshots and an administrative moderation queue.
- Participant-only conversation reports with categorized reasons, immutable message cutoffs, and duplicate-report protection.
- Owner-controlled active and closed states.
- Permission-based moderation with a protected hidden state.
- English and Spanish (`es_ES`) translations.
- Initial review data model, intentionally not exposed until a verified collaboration workflow exists.

## Installation

Place this repository at `plugins/seeker`, then enable **Seeker** from the Azuriom administration panel. Azuriom runs the plugin migrations and publishes the plugin assets as part of its normal enable flow.

Video and audio uploads require PHP's `upload_max_filesize` to be at least `10M` and `post_max_size` to be greater than `10M` (for example, `12M`) so multipart request overhead does not reduce Seeker's effective limit.

Grant `Moderate Seeker publications` only to roles that should be able to hide or restore publications.

## Data and files

All plugin tables use the `seeker_` prefix. Uploaded references are stored on Laravel's private `local` disk below `seeker/publications` and are served through a controlled plugin route.

The plugin does not modify Azuriom core files, root routes, root build configuration, or generated public assets.

## Roadmap

The next domain milestone is the dispute and refund workflow for completion requests that cannot be resolved by the participants. Additional planned work includes review moderation, an administrative report-review interface, talent categories, richer profiles, and configurable moderation settings.

## Development checks

Before releasing a new version:

1. Validate `plugin.json` and `composer.json`.
2. Run PHP syntax checks and compile every Blade view.
3. Run fresh migration and rollback smoke tests on SQLite and the production database engine.
4. Verify guest, verified-user, owner, moderator, and unauthorized-user flows.
5. Test image, video, and audio upload, rendering, replacement, publication deletion, plugin enable/disable, and route caching.
6. Test with both the default Azuriom theme and the target custom theme.
