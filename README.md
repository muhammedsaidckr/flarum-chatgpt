# ChatGPT

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/muhammedsaidckr/flarum-chatgpt.svg)](https://packagist.org/packages/muhammedsaidckr/flarum-chatgpt) [![Total Downloads](https://img.shields.io/packagist/dt/muhammedsaidckr/flarum-chatgpt.svg)](https://packagist.org/packages/muhammedsaidckr/flarum-chatgpt)

A [Flarum](http://flarum.org) extension. This extension integrates the power of ChatGPT within the Flarum platform, enabling seamless AI-powered conversation capabilities for users.

## Installation

Install with composer:

```sh
composer require muhammedsaidckr/flarum-chatgpt:"*"
```

## Updating

```sh
composer update muhammedsaidckr/flarum-chatgpt:"*"
php flarum migrate
php flarum cache:clear
```

## Settings 

[//]: # (PUT IMAGE URLS https://ibb.co/XDvYrvj, https://ibb.co/Np0ksjq)
![Settings](https://i.ibb.co/xYRFtRX/Screenshot-from-2024-06-11-16-28-39.png)

If you set Answer Duration (Cevap Verme Suresi) value to greater than 0 then, set the database queue extension retry count (Yeniden denemeler) to 5 or greater.  

![Settings](https://i.ibb.co/vmNrHPW/Screenshot-from-2024-06-11-16-27-22.png)

### GPT-5 Support (v1.4.0+)

Starting from v1.4.0, this extension supports GPT-5 models (`gpt-5`, `gpt-5-mini`, `gpt-5-nano`) using the OpenAI Responses API.

#### Configuration
When using a GPT-5 model, the following settings become active:
- **Reasoning Effort**: Controls the depth of reasoning before responding (Minimal, Low, Medium, High).
- **Output Verbosity**: Controls the detail and length of the response (Low, Medium, High).

#### Migration Guide
To upgrade to GPT-5 support:
1. Update the package: `composer update muhammedsaidckr/flarum-chatgpt:"*"`
2. Run migrations to create the Chain of Thought (CoT) storage: `php flarum migrate`
3. Clear cache: `php flarum cache:clear`
4. In the Admin panel, select a GPT-5 model and configure the reasoning and verbosity settings.

Backward compatibility is maintained for all other models.


## Links

- [Packagist](https://packagist.org/packages/muhammedsaidckr/flarum-chatgpt)
- [GitHub](https://github.com/muhammedsaidckr/flarum-chatgpt)
- [Discuss](https://discuss.flarum.org/d/PUT_DISCUSS_SLUG_HERE)
