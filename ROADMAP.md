# Flarum ChatGPT Extension - Roadmap

## Current Status (v1.3.3)

✅ **Completed:**
- OpenAI reasoning model support (o1, o3, o4 series)
- Chat Completions API fully implemented
- Comprehensive error logging
- Support for legacy models (gpt-3.5, gpt-4, gpt-4o)
- System prompt handling for both reasoning and legacy models

## Future Development

### 1. GPT-5 Responses API Support (Planned for v1.4.0)

**Priority:** High
**Complexity:** High
**Estimated Effort:** 3-5 days

#### Background
GPT-5 models (`gpt-5-2025-08-07`, `gpt-5-mini-2025-08-07`, `gpt-5-nano-2025-08-07`) require the **Responses API** instead of the Chat Completions API. This is a fundamentally different endpoint with different parameters and capabilities.

#### Key Differences: Chat Completions vs Responses API

| Feature | Chat Completions API | Responses API (GPT-5) |
|---------|---------------------|----------------------|
| **Endpoint** | `/v1/chat/completions` | `/v1/responses` |
| **Model Support** | gpt-3.5, gpt-4, o1, o3, o4 | gpt-5, gpt-5-mini, gpt-5-nano |
| **Token Parameter** | `max_tokens` or `max_completion_tokens` | `max_output_tokens` |
| **Temperature** | ✅ Supported | ❌ Not supported |
| **top_p** | ✅ Supported | ❌ Not supported |
| **Reasoning Control** | N/A | `reasoning: { effort: "minimal"\|"low"\|"medium"\|"high" }` |
| **Output Verbosity** | N/A | `text: { verbosity: "low"\|"medium"\|"high" }` |
| **Chain of Thought** | ❌ Not passed between turns | ✅ Passed between turns |
| **System Role** | ✅ Supported | ✅ Supported |

#### Implementation Plan

##### Phase 1: Research & Design (1 day)
- [ ] Study OpenAI Responses API documentation
- [ ] Analyze PHP client library support for Responses API
- [ ] Design abstraction layer for dual API support
- [ ] Define configuration interface for GPT-5 parameters

##### Phase 2: Core Implementation (2 days)
- [ ] Create `ResponsesApiClient` class
- [ ] Implement GPT-5 model detection (`isGpt5Model()`)
- [ ] Add parameter mapping for Responses API:
  ```php
  // GPT-5 specific parameters
  'max_output_tokens' => $maxTokens,
  'reasoning' => ['effort' => 'medium'],
  'text' => ['verbosity' => 'medium']
  ```
- [ ] Remove unsupported parameters (temperature, top_p) for GPT-5
- [ ] Implement Chain of Thought (CoT) storage and retrieval
- [ ] Handle response format differences

##### Phase 3: Configuration & Settings (1 day)
- [ ] Add GPT-5-specific settings to admin panel:
  - Reasoning effort level (minimal, low, medium, high)
  - Output verbosity (low, medium, high)
  - Max output tokens
- [ ] Create migration for new settings
- [ ] Update frontend components for GPT-5 settings

##### Phase 4: Testing & Documentation (1 day)
- [ ] Unit tests for GPT-5 parameter handling
- [ ] Integration tests with GPT-5 models
- [ ] Update CLAUDE.md with GPT-5 implementation details
- [ ] Create user documentation for GPT-5 configuration
- [ ] Add migration guide from older models to GPT-5

#### Technical Architecture

```php
// Proposed structure
class Agent
{
    private function sendRequest(array $messages)
    {
        if ($this->isGpt5Model()) {
            return $this->sendResponsesApiRequest($messages);
        } elseif ($this->isReasoningModel()) {
            return $this->sendChatCompletionRequest($messages, 'max_completion_tokens');
        } else {
            return $this->sendChatCompletionRequest($messages, 'max_tokens');
        }
    }

    private function sendResponsesApiRequest(array $messages)
    {
        $params = [
            'model' => $this->model,
            'messages' => $messages,
            'max_output_tokens' => $this->maxTokens,
            'reasoning' => [
                'effort' => $this->getReasoningEffort() // from settings
            ],
            'text' => [
                'verbosity' => $this->getVerbosity() // from settings
            ]
        ];

        // Store and retrieve CoT for better responses
        if ($previousCoT = $this->getStoredCoT($discussionId)) {
            $params['previous_cot'] = $previousCoT;
        }

        $response = $this->client->responses()->create($params);

        // Store CoT for next turn
        if ($response->cot) {
            $this->storeCoT($discussionId, $response->cot);
        }

        return $response;
    }

    private function isGpt5Model(): bool
    {
        return str_contains(strtolower($this->model), 'gpt-5');
    }
}
```

#### Chain of Thought (CoT) Storage

GPT-5's main advantage is passing CoT between turns. We need to:

1. **Store CoT per discussion:**
   ```php
   // New table: flarum_chatgpt_cot
   Schema::create('chatgpt_cot', function (Blueprint $table) {
       $table->id();
       $table->unsignedInteger('discussion_id');
       $table->text('cot'); // Chain of thought from previous response
       $table->timestamp('created_at');

       $table->foreign('discussion_id')->references('id')->on('discussions')->onDelete('cascade');
       $table->index('discussion_id');
   });
   ```

2. **Update CoT on each response:**
   - Extract CoT from Responses API response
   - Store in database linked to discussion
   - Pass to next request for better context

#### Migration Strategy

**For users upgrading from v1.3.3 to v1.4.0:**

1. Update package: `composer require muhammedsaidckr/flarum-chatgpt:^1.4.0`
2. Run migrations: `php flarum migrate`
3. Clear cache: `php flarum cache:clear`
4. Configure GPT-5 settings in admin panel
5. Switch model to GPT-5 variant

**Backward Compatibility:**
- All existing models (gpt-3.5, gpt-4, o1) continue to work
- No breaking changes to existing functionality
- GPT-5 is opt-in via model selection

---

### 2. Enhanced Model Management (Planned for v1.5.0)

**Priority:** Medium
**Complexity:** Medium
**Estimated Effort:** 2-3 days

- [ ] Model presets (default configurations per model)
- [ ] Automatic model detection and recommendation
- [ ] Model performance analytics
- [ ] Cost tracking per model
- [ ] Response quality metrics

### 3. Advanced Features (Future)

**Multi-turn Context Enhancement:**
- [ ] Conversation summarization for long discussions
- [ ] Semantic search for relevant context
- [ ] User preference learning

**Response Quality:**
- [ ] Response validation and regeneration
- [ ] Multiple response generation with voting
- [ ] Tone and style customization

**Performance:**
- [ ] Response caching
- [ ] Async processing improvements
- [ ] Batch processing for multiple discussions

---

## Contributing

We welcome contributions to any of these roadmap items! Please:

1. Check existing issues/PRs for the feature
2. Open an issue to discuss your approach
3. Submit a PR referencing the roadmap item

## Version History

- **v1.3.3** (Current) - Reasoning model support, comprehensive logging
- **v1.3.2** - User ID validation fix
- **v1.3.1** - Fatal error fixes
- **v1.3.0** - Queue improvements
- **v1.2.0** - Tag filtering support
- **v1.1.0** - Reply continuation feature
- **v1.0.0** - Initial release

## Questions?

- 📧 Email: muhammedsaidckr@gmail.com
- 🐛 Issues: https://github.com/muhammedsaidckr/flarum-chatgpt/issues
- 💬 Discussions: https://github.com/muhammedsaidckr/flarum-chatgpt/discussions
