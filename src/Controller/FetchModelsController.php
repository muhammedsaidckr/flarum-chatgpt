<?php

namespace Msc\ChatGPT\Controller;

use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use OpenAI\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FetchModelsController implements RequestHandlerInterface
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected ?Client $client = null
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertAdmin();

        if (!$this->client) {
            return new JsonResponse([
                'error' => 'OpenAI client not configured. Please check your API key and base URI settings.'
            ], 400);
        }

        try {
            // Fetch models from OpenAI API
            $response = $this->client->models()->list();

            // Filter for chat-compatible models (gpt-*, chatgpt-*, o1-*, etc.)
            $chatModels = array_filter($response->data, function ($model) {
                $id = $model->id;
                return str_starts_with($id, 'gpt-')
                    || str_starts_with($id, 'chatgpt-')
                    || str_starts_with($id, 'o1-');
            });

            // Sort models by created date (newest first)
            usort($chatModels, function ($a, $b) {
                return $b->created - $a->created;
            });

            // Convert to simple array of model objects
            $models = array_map(function ($model) {
                return [
                    'id' => $model->id,
                    'created' => $model->created,
                    'owned_by' => $model->owned_by ?? 'unknown'
                ];
            }, $chatModels);

            // Store in settings as JSON
            $this->settings->set('muhammedsaidckr-chatgpt.cached_models', json_encode($models));
            $this->settings->set('muhammedsaidckr-chatgpt.models_last_fetched', time());

            return new JsonResponse([
                'models' => $models,
                'count' => count($models),
                'last_fetched' => time()
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch models: ' . $e->getMessage()
            ], 500);
        }
    }
}
