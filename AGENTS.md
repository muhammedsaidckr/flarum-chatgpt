# AGENTS.md

## Developer commands

### Backend (PHP)
```bash
docker compose exec php composer install                    # install PHP deps
docker compose -f docker-compose.test.yml run --rm tests composer test:unit                       # run all tests (unit + integration)
docker compose exec php composer test:unit                  # unit tests only
docker compose exec php composer test:integration           # integration tests only (requires DB setup)
docker compose exec php composer test:setup                 # one-time integration test DB setup
```

### Frontend (JS)
```bash
cd js/
npm install                         # install JS deps
npm run dev                         # dev build + watch
npm run build                       # production build
npm run format                      # format with prettier
npm run format-check                # check formatting
```

**Note:** TypeScript typings and type-checking scripts are disabled (`echo 'Typings disabled'`). Do not attempt to run `check-typings` or `build-typings`.

## Architecture

- **Type:** Flarum extension (`flarum-extension`), not a standalone app
- **Namespace:** `Msc\ChatGPT` → `src/`
- **Package:** `muhammedsaidckr/flarum-chatgpt`
- **Entry point:** `extend.php` — registers all event listeners, middleware, routes, frontend assets, and settings defaults

### Backend data flow
1. User creates discussion → `Started` event → `ReplyToPost` listener → `Agent::repliesTo()`
2. User posts comment → `Posted` event → `ReplyToCommentPost` listener → `Agent::repliesToCommentPost()`
3. Both support queue (`ReplyJob`/`ReplyPostJob`) or immediate execution via `queue_active` setting

### Key quirk: reasoning models
`Agent::isReasoningModel()` checks for `o1`, `o3`, `o4`, `gpt-5` in the model name. Reasoning models:
- Use `max_completion_tokens` instead of `max_tokens`
- Omit `system` role — system prompt is prepended to the first `user` message

### Settings prefix
All Flarum settings use prefix `muhammedsaidckr-chatgpt.` (e.g., `muhammedsaidckr-chatgpt.model`).

### Frontend
- `js/src/admin/` — admin settings panel (`ChatGptSettings.tsx`)
- `js/src/forum/` — forum-side components
- `js/src/common/` — shared module
- Built with webpack via `flarum-webpack-config`; output goes to `js/dist/`

## Testing

- Unit tests: `tests/unit/` (currently empty — `.gitkeep` only)
- Integration tests: `tests/integration/` — requires running `composer test:setup` once first to create the test DB
- Integration test config has `processIsolation="true"` (spawns separate PHP processes)
- Test file suffix: `*Test.php`

## CI

- Backend CI uses `flarum/framework` reusable workflow — PHP 8.1/8.2/8.3, backend testing enabled
- Frontend CI uses `flarum/framework` reusable workflow — prettier enabled, TypeScript/bundlewatch disabled
- Default branch: `main`

## Dependencies

- PHP: `flarum/core` ^1.2.0, `openai-php/client` ^0.16.0 (note: CLAUDE.md says ^0.8.0 but composer.json has ^0.16.0)
- JS: `flarum-webpack-config` ^2.0.0, TypeScript ^4.5.4, prettier with `@flarum/prettier-config`
