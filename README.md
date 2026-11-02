# aiprovider_xai

Moodle AI provider for [xAI](https://x.ai) (Grok). It follows the core `aiprovider_openai` provider from Moodle 5.3: provider class, per-action processors, action settings forms, predefined model classes, hook listeners, privacy metadata, and PHPUnit tests that mock HTTP.

Supported Moodle versions are **5.1, 5.2 and 5.3** only (`$plugin->requires = 2025100600`, `$plugin->supported = [501, 503]`).

## Actions

| Action | Endpoint | Notes |
| --- | --- | --- |
| Generate text | `POST https://api.x.ai/v1/chat/completions` | Default model `grok-4.5` |
| Summarise text | same chat completion | Same processor as generate text. The action supplies the system instruction. |
| Explain text | same chat completion | Same as summarise. Core OpenAI treats explain text this way in 5.3, and the class exists on 5.1 as well. |
| Generate image | `POST https://api.x.ai/v1/images/generations` | Default model `grok-imagine-image-2.0`. Response format is `b64_json` because returned URLs are temporary. |

Default system instructions are one short sentence each: plain text, and the same language as the input (the user's request for generate text). Explain asks for an explanation, not a summary. An instruction that still matches the core text, or the longer default this plugin shipped earlier, is replaced when the action runs and on upgrade. Text an admin has edited is left as saved.

Removed preset ids `grok-4.3` and `grok-imagine-image-quality` are migrated to `grok-4.5` and `grok-imagine-image-2.0`. The settings form shows that new preset, not Custom. Any other custom id is left as entered. A site that already selected `grok-4.7` or `grok-4.6` keeps that id.

Chat history, vision input, and image edits are out of scope.

Rate limiting is the core provider limiter (`enableuserratelimit`, `userratelimit`, `enableglobalratelimit`, `globalratelimit`). This plugin does not implement its own.

## Provider setting

Each provider instance has one required setting: **API key** (`passwordunmask`). Requests send `Authorization: Bearer <key>`.

## Predefined models

Text (checked against [docs.x.ai](https://docs.x.ai/developers/models) on 2 November 2026):

| Model id | Why it is predefined |
| --- | --- |
| `grok-4.5` | Default for generate, summarise, and explain text. |
| `grok-4.7` | Flagship. Selectable. Real Moodle calls spent more reasoning tokens than 4.5. |
| `grok-4.6` | Current model. Selectable, same sampling settings as 4.7. |

The default is `grok-4.5`. List prices for prompts under 200k tokens are the same $2 / $6 per 1M tokens on `grok-4.7`, `grok-4.6` and `grok-4.5`, but a real generate-text call cost about $0.0012 on 4.5 and about $0.0034 on 4.7 because 4.7 used more reasoning tokens. `grok-4.3` is not a preset. Enter it, or `grok-build-0.1`, in the custom model field. Older slugs retired on 15 May 2026 (`grok-3`, `grok-4-0709`, and the fast variants) redirect to `grok-4.3` and are not predefined.

Image:

| Model id | Why it is predefined |
| --- | --- |
| `grok-imagine-image-2.0` | Current Imagine image model. Default. Accepts `quality`. |
| `grok-imagine-image` | Imagine 1.0. Still served and lower priced. `quality` is documented only for 2.0, so this model omits it. |

`grok-imagine-image-quality` was retired on 2 November 2026. Requests to that slug are served by `grok-imagine-image-2.0` with `quality` `low`. It is not a predefined option. `grok-imagine-image-pro` already follows that redirect.

Every action also has a **custom** model option. Extra parameters are a JSON object, same as the OpenAI provider.

Text model settings, where the chat completions API accepts them: `temperature` (0–2), `top_p`, `max_completion_tokens`. `max_tokens` is deprecated on xAI. `frequency_penalty` and `presence_penalty` are not offered on these predefined models because the API does not support them on Grok reasoning models. A custom model can still send them via extra parameters.

Image model setting: `resolution`, `1k` (API default) or `2k`. The API also accepts `1.5k`; send that through extra parameters on a custom model.

## Image parameter mapping

Moodle `generate_image` inputs mapped onto the xAI body:

| Moodle input | xAI parameter | Mapping |
| --- | --- | --- |
| `prompttext` | `prompt` | Unchanged. |
| `numimages` | `n` | Ignored. Moodle stores one image, so the request always sends `n` = 1. |
| `aspectratio` `square` | `aspect_ratio` | `1:1` |
| `aspectratio` `landscape` | `aspect_ratio` | `16:9` |
| `aspectratio` `portrait` | `aspect_ratio` | `9:16` |
| `quality` `standard` | `quality` | `low` (2.0 only) |
| `quality` `hd` | `quality` | `medium` (2.0 only) |
| `style` | — | Not sent. The generations API has no style parameter. |
| — | `resolution` | Not an action input. Per-model setting, `1k` or `2k`. Omitted unless configured; the API then uses `1k`. |
| — | `response_format` | Always `b64_json`. |

xAI quality values are `low`, `medium`, and `auto`. There is no `high`. Moodle only offers `standard` and `hd`, so those map to `low` and `medium` in that order. `auto` is left unused because it currently serves `low` for generation and would collapse the two Moodle choices.

The file extension comes from `mime_type` (`image/jpeg` → `jpg`, `image/png` → `png`, `image/webp` → `webp`). When `mime_type` is absent the extension is `jpg`, which is what the API returns unless quality produces PNG.

## Privacy

The plugin stores no personal data. Prompts and the related request fields are declared as external-location metadata sent to xAI, in the same shape as the OpenAI provider.

## Tests

PHPUnit covers the provider, generate text, summarise text, and generate image. HTTP is mocked. No test calls api.x.ai.

```bash
moodle-plugin-ci phpunit
```

## 5.1, 5.2 and 5.3

`$plugin->supported = [501, 503]` covers 5.1, 5.2 and 5.3. GitHub Actions runs the same suite on `MOODLE_501_STABLE` (PHP 8.2), `MOODLE_502_STABLE` (PHP 8.3) and `MOODLE_503_STABLE` (PHP 8.3).

The provider contract this plugin uses (`core_ai\provider`, `process_base`, action settings forms, model hooks) is unchanged from 5.2 to 5.3. Compared with `MOODLE_503_STABLE` on 2 November 2026, the core AI change that touches a provider response is [MDL-89123](https://tracker.moodle.org/browse/MDL-89123): `prompttokens` and `completiontokens` move from the action child tables to `ai_action_register`, and `core_ai\manager::store_action_result()` reads them from the response. This plugin still returns both keys. On 5.1 and 5.2 the action `store()` method writes them; on 5.3 the manager does. [MDL-80893](https://tracker.moodle.org/browse/MDL-80893) adds `courseid` on the register inside core and does not change the provider API.

The `aiprovider_openai` PHP classes are the same on `MOODLE_501_STABLE` and `MOODLE_503_STABLE`. This plugin uses that layout, including `#[\Override]` and PHPUnit `CoversClass` attributes. Both are valid on Moodle 5.1: PHP 8.2 ignores `\Override`, and Moodle 5.1 ships PHPUnit 11, which accepts the attributes. The OpenAI text action form in 5.3 references an undefined `$actionconfig` when setting defaults; this plugin uses `$this->actionconfig`, which is what the image form in core already does.
