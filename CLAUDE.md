# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Flarum extension that integrates ChatGPT/OpenAI API capabilities into the Flarum forum platform. The extension enables AI-powered conversation capabilities by having a designated user (assistant) automatically reply to discussions and comments.

**Package:** `muhammedsaidckr/flarum-chatgpt`
**Namespace:** `Msc\ChatGPT`

## Commands

### PHP/Backend
```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run integration tests only
composer test:integration

# Set up integration test database (run once)
composer test:setup

# Update extension
composer update muhammedsaidckr/flarum-chatgpt:"*"
php flarum migrate
php flarum cache:clear
```

### JavaScript/Frontend
```bash
cd js/

# Install dependencies
npm install

# Development build with watch
npm run dev

# Production build
npm run build

# Format code
npm run format

# Check formatting
npm run format-check

# Type checking
npm run check-typings

# Build TypeScript typings
npm run build-typings
```

## Architecture

### Backend (PHP)

**Core Components:**

- **Agent (`src/Agent.php`)**: Main AI interaction class that handles communication with OpenAI API
  - `repliesTo(Discussion)`: Generates AI response to new discussions
  - `repliesToCommentPost(CommentPost)`: Generates AI response to comment posts
  - `checkModeration()`: Uses OpenAI moderation API to check for inappropriate content
  - Manages conversation history by building message arrays from discussion posts

- **Service Providers:**
  - `ClientProvider`: Registers OpenAI client singleton with API key and base URI from settings
  - `BindingsProvider`: Registers service bindings

- **Event Listeners:**
  - `ReplyToPost`: Listens to `Discussion\Event\Started`, triggers AI response when new discussion is created
  - `ReplyToCommentPost`: Listens to `Post\Event\Posted`, triggers AI response to comments
  - Both support queue-based and immediate execution modes

- **Jobs:**
  - `ReplyJob`: Queue job for discussion responses
  - `ReplyPostJob`: Queue job for comment responses

- **Middleware:**
  - `ModerationMiddleware`: Handles content moderation across all middleware groups (api, forum, admin, frontend)

**Extension Registration (`extend.php`):**
- Registers middleware, frontend assets, locales, service providers, event listeners, and default settings
- Key settings include model selection, token limits, prompts, queue activation, and moderation flags

### Frontend (TypeScript/JavaScript)

**Structure:**
- `js/src/admin/`: Admin panel components and settings page
- `js/src/forum/`: Forum-side components
- `js/src/common/`: Shared components

**Admin Panel:**
- Registers permission `discussion.useChatGPTAssistant` to control who can use the ChatGPT assistant feature
- Custom settings page via `ChatGptSettings` component for configuring API key, model, prompts, etc.

### Key Configuration Settings

Settings are stored in Flarum settings repository with prefix `muhammedsaidckr-chatgpt.`:
- `api_key`: OpenAI API key
- `base_uri`: API base URI (default: https://api.openai.com/v1/)
- `model`: GPT model to use (default: gpt-3.5-turbo-instruct)
- `max_tokens`: Maximum tokens in response (default: 100)
- `role`: System role for the AI (default: "You are a forum user")
- `prompt`: Template for generating responses with placeholders: [title], [content], [language]
- `user_prompt`: User ID of the assistant account that posts AI responses
- `queue_active`: Whether to use queue for processing (default: true)
- `answer_duration`: Delay in minutes before responding (default: 0)
- `continue_to_reply`: Whether AI continues replying in threads
- `continue_to_reply_count`: Max number of AI replies per discussion (default: 5)
- `moderation`: Enable OpenAI moderation checks (default: false)
- `enabled-tags`: JSON array of tag IDs where the assistant is enabled

### Data Flow

1. User creates discussion or posts comment
2. Event listener (`ReplyToPost` or `ReplyToCommentPost`) catches event
3. Checks permissions, tag filters, and reply limits
4. If queue is enabled, dispatches job; otherwise executes immediately
5. `Agent` builds message array from discussion history
6. Makes OpenAI API request via `openai-php/client` library
7. Saves AI response as a CommentPost from the designated assistant user

### Testing

- Uses `flarum/testing` package
- Separate unit and integration test configurations in `tests/`
- Integration tests require database setup via `composer test:setup`

### Dependencies

**PHP:**
- `flarum/core`: ^1.2.0
- `openai-php/client`: ^0.8.0

**JavaScript:**
- Webpack-based build with TypeScript support
- Follows Flarum's frontend architecture with admin and forum modules
