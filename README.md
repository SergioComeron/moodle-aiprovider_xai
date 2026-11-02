# xAI provider for Moodle

`aiprovider_xai` connects Moodle's AI subsystem to the [xAI API](https://x.ai) (Grok). It supports the core text and image actions used by Moodle placements.

This is an unofficial plugin. It is not affiliated with, endorsed by, or supported by xAI or by Moodle.

## Requirements

- Moodle 5.1, 5.2, or 5.3 (`$plugin->requires = 2025100600`, `$plugin->supported = [501, 503]`).
- PHP as required by that Moodle version.
- An xAI API key. xAI is a paid service: API use is billed to the account that owns the key.

## Installation

1. Download the plugin ZIP, or clone this repository.
2. Install it from Site administration → Plugins → Install plugins, or unzip it into `ai/provider/xai` (the folder must be named `xai`).
3. Open Site administration → Notifications to finish the install.

No extra plugins and no Composer step are required.

## API key

1. Sign in at the [xAI console](https://console.x.ai/).
2. Create an API key.
3. In Moodle, go to Site administration → General → AI → AI providers.
4. Create an instance of this provider and paste the key.

Keep the key secret. Requests send it as `Authorization: Bearer <key>`. Usage is charged by xAI according to your account.

## Configuration

Each provider instance has one required setting, the API key.

Each supported action has its own settings: model, endpoint, and, for text actions, a system instruction. The text actions default to `grok-4.5`. Image generation defaults to `grok-imagine-image-2.0`. The model can be changed in the action settings: pick another preset or type a custom model id. Presets follow what the xAI API currently serves and can change; the custom field is there so a site is not stuck on a retired id.

Text actions can also set sampling options such as temperature, top p, and maximum completion tokens. Image generation can set resolution (`1k` or `2k` on the current default image model).

## Actions and placements

The plugin does not add its own buttons. Moodle placements call the core AI actions, and this provider handles them when it is enabled and ordered for that action.

| Action | What it does |
| --- | --- |
| Generate text | Chat completion from the user's prompt. |
| Summarise text | Same chat completion. The action supplies a short summary instruction. |
| Explain text | Same chat completion. The instruction asks for an explanation, not a summary. |
| Generate image | Image generation. Moodle stores one image, so the request asks for one image. Returned URLs expire, so the plugin requests base64. |

Typical placements are the text editor (generate text and generate image) and course assistance (summarise and explain). Enable the action on the provider, then enable it on the placement.

Chat history, vision input, and image editing are not supported.

Rate limits are Moodle's provider limits (per user and site-wide, per hour).

## Privacy

The plugin does not store personal data in its own tables. Prompts and related request fields are sent to xAI and are declared in the Privacy API as data in an external location.

xAI privacy policy: https://x.ai/legal/privacy-policy

That policy describes how xAI handles personal information. Data sent through the API is also subject to the terms of the xAI account that owns the key.

## Tracker

Bugs and feature requests: https://github.com/SergioComeron/moodle-aiprovider_xai/issues

## Licence

GNU GPL v3 or later. See [LICENSE](LICENSE).
