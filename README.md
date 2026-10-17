# aiprovider_xai

Moodle AI provider for [xAI](https://x.ai) (Grok). It follows the core `aiprovider_openai` provider from Moodle 5.3: provider class, per-action processors, action settings forms, predefined model classes, hook listeners, privacy metadata, and PHPUnit tests that mock HTTP.

Supported Moodle versions are **5.1, 5.2 and 5.3** only (`$plugin->requires = 2025100600`, `$plugin->supported = [501, 503]`).

## Actions

| Action | Endpoint | Notes |
| --- | --- | --- |
| Generate text | `POST https://api.x.ai/v1/chat/completions` | Default model `grok-4.7` |
| Summarise text | same chat completion | Same processor as generate text. The action supplies the system instruction. |
| Explain text | same chat completion | Same as summarise. Core OpenAI treats explain text this way in 5.3, and the class exists on 5.1 as well. |
| Generate image | `POST https://api.x.ai/v1/images/generations` | Default model `grok-imagine-image-2.0`. Response format is `b64_json` because returned URLs are temporary. |

Chat history, vision input, and image edits are out of scope.

Rate limiting is the core provider limiter (`enableuserratelimit`, `userratelimit`, `enableglobalratelimit`, `globalratelimit`). This plugin does not implement its own.

## Provider setting

Each provider instance has one required setting: **API key** (`passwordunmask`). Requests send `Authorization: Bearer <key>`.

## Predefined models

Text (chosen from the models listed on [docs.x.ai](https://docs.x.ai/developers/models) in October 2026):

| Model id | Why it is predefined |
| --- | --- |
| `grok-4.7` | Current flagship. Default for generate, summarise, and explain text. |
| `grok-4.5` | Previous flagship, still on the pricing page, so a site can pin it. |
| `grok-4.3` | Lower token price and a 1M context window. Sensible choice for lighter text actions. |

`grok-4.6` is also current and priced like 4.7. It is not a separate predefined class; use the custom model field for that id.

Image:

| Model id | Why it is predefined |
| --- | --- |
| `grok-imagine-image-2.0` | Current Imagine image model. Default. Accepts `quality`. |
| `grok-imagine-image` | Lower-priced Imagine model still on the pricing page. `quality` is documented only for 2.0, so this model omits it. |

Every action also has a **custom** model option. Extra parameters are a JSON object, same as the OpenAI provider.

Text model settings, where the chat completions API accepts them: `temperature` (0–2), `top_p`, `max_completion_tokens`. `max_tokens` is deprecated on xAI. `frequency_penalty` and `presence_penalty` are not offered on these predefined models because the API does not support them on Grok reasoning models. A custom model can still send them via extra parameters.

Image model setting: `resolution`, `1k` (API default) or `2k`. The API also accepts `1.5k`; send that through extra parameters on a custom model.

## Image parameter mapping

Moodle `generate_image` inputs mapped onto the xAI body:

| Moodle input | xAI parameter | Mapping |
| --- | --- | --- |
| `prompttext` | `prompt` | Unchanged. |
| `numimages` | `n` | Clamped to 1–10. The core response stores one draft file, so only `data[0]` is watermarked and returned. |
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

## 5.1 and 5.3

The `aiprovider_openai` PHP classes are the same on `MOODLE_501_STABLE` and `MOODLE_503_STABLE`. This plugin uses that 5.3 layout, including `#[\Override]` and PHPUnit `CoversClass` attributes. Both are valid on Moodle 5.1: PHP 8.2 ignores `\Override`, and Moodle 5.1 ships PHPUnit 11, which accepts the attributes. The OpenAI text action form in 5.3 references an undefined `$actionconfig` when setting defaults; this plugin uses `$this->actionconfig`, which is what the image form in core already does.
