# Seeker

Seeker is an Azuriom talent portal where community members can offer freelance commissions or look for collaborators for their projects.

## Requirements

- Azuriom 1.2.x (extension API 1.2.0)
- PHP 8.2 or newer

## Current features

- Public catalog with text search and publication-type filters.
- Authenticated creation and management of commission offers and talent searches.
- One required portfolio format per publication: an HTTP/HTTPS external link or uploaded reference images, never both.
- Between one and six privately stored images when the uploaded portfolio format is selected (JPG, PNG, or WebP, 5 MB each).
- Per-publication visibility for everyone or authenticated Zibuu members only.
- Informational service pricing as fixed or hourly Zibuu points, free, or to be agreed between interested users.
- Private one-to-one conversations between publication authors and interested members.
- Transactional point holds for fixed-price services, with duplicate-contact protection and no early delivery to the author.
- Owner-controlled active and closed states.
- Permission-based moderation with a protected hidden state.
- English and Spanish (`es_ES`) translations.
- Initial review data model, intentionally not exposed until a verified collaboration workflow exists.

## Installation

Place this repository at `plugins/seeker`, then enable **Seeker** from the Azuriom administration panel. Azuriom runs the plugin migrations and publishes the plugin assets as part of its normal enable flow.

Grant `Moderate Seeker publications` only to roles that should be able to hide or restore publications.

## Data and files

All plugin tables use the `seeker_` prefix. Uploaded references are stored on Laravel's private `local` disk below `seeker/publications` and are served through a controlled plugin route.

The plugin does not modify Azuriom core files, root routes, root build configuration, or generated public assets.

## Roadmap

The next domain milestone is the completion and dispute workflow that releases held points to the author or refunds them to the client. Reviews and reputation should only become writable after a collaboration is completed; this prevents arbitrary or self-issued ratings. Additional planned work includes hourly-work agreements, talent categories, richer profiles, reporting, and configurable moderation settings.

## Development checks

Before releasing a new version:

1. Validate `plugin.json` and `composer.json`.
2. Run PHP syntax checks and compile every Blade view.
3. Run fresh migration and rollback smoke tests on SQLite and the production database engine.
4. Verify guest, verified-user, owner, moderator, and unauthorized-user flows.
5. Test image upload, rendering, removal, publication deletion, plugin enable/disable, and route caching.
6. Test with both the default Azuriom theme and the target custom theme.
