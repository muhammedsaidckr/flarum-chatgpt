<?php

namespace Msc\ChatGPT;

class ModelCatalog
{
    public function isGpt5Model(string $model): bool
    {
        return str_contains(strtolower($model), 'gpt-5');
    }

    public function isReasoningModel(string $model): bool
    {
        $modelLower = strtolower($model);
        $reasoningPatterns = ['o1', 'o3', 'o4', 'gpt-5'];

        foreach ($reasoningPatterns as $pattern) {
            if (str_contains($modelLower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public function getFamily(string $model): string
    {
        $modelLower = strtolower($model);

        if (str_starts_with($modelLower, 'gpt-5-mini')) {
            return 'gpt-5-mini';
        }

        if (str_starts_with($modelLower, 'gpt-5-nano')) {
            return 'gpt-5-nano';
        }

        if (str_starts_with($modelLower, 'gpt-5')) {
            return 'gpt-5';
        }

        if (str_starts_with($modelLower, 'gpt-4o')) {
            return 'gpt-4o';
        }

        if (str_starts_with($modelLower, 'o')) {
            return 'o-series';
        }

        return 'legacy';
    }

    public function getApiMode(string $model): string
    {
        return $this->isGpt5Model($model) ? 'responses' : 'chat_completions';
    }

    public function getPresetForModel(string $model): array
    {
        $family = $this->getFamily($model);

        return match ($family) {
            'gpt-5' => [
                'max_tokens' => 1800,
                'gpt5_reasoning_effort' => 'medium',
                'gpt5_verbosity' => 'medium',
            ],
            'gpt-5-mini' => [
                'max_tokens' => 1200,
                'gpt5_reasoning_effort' => 'medium',
                'gpt5_verbosity' => 'medium',
            ],
            'gpt-5-nano' => [
                'max_tokens' => 700,
                'gpt5_reasoning_effort' => 'low',
                'gpt5_verbosity' => 'low',
            ],
            'o-series' => [
                'max_tokens' => 900,
                'gpt5_reasoning_effort' => 'medium',
                'gpt5_verbosity' => 'medium',
            ],
            'gpt-4o' => [
                'max_tokens' => 1000,
                'gpt5_reasoning_effort' => 'medium',
                'gpt5_verbosity' => 'medium',
            ],
            default => [
                'max_tokens' => 800,
                'gpt5_reasoning_effort' => 'medium',
                'gpt5_verbosity' => 'medium',
            ],
        };
    }

    public function recommend(array $models, ?string $currentModel = null): ?string
    {
        $modelIds = array_values(array_filter(array_map(function ($model) {
            if (is_array($model)) {
                return $model['id'] ?? null;
            }

            if (is_object($model)) {
                return $model->id ?? null;
            }

            if (is_string($model)) {
                return $model;
            }

            return null;
        }, $models)));

        if (empty($modelIds)) {
            return null;
        }

        $priorityPrefixes = ['gpt-5-mini', 'gpt-5', 'gpt-4o', 'o'];

        foreach ($priorityPrefixes as $prefix) {
            foreach ($modelIds as $modelId) {
                if (str_starts_with(strtolower($modelId), $prefix)) {
                    return $modelId;
                }
            }
        }

        if ($currentModel && in_array($currentModel, $modelIds, true)) {
            return $currentModel;
        }

        return $modelIds[0];
    }

    public function buildMetadata(array $models): array
    {
        $metadata = [];

        foreach ($models as $model) {
            $modelId = is_array($model) ? ($model['id'] ?? null) : ($model->id ?? null);
            if (!$modelId) {
                continue;
            }

            $metadata[$modelId] = [
                'family' => $this->getFamily($modelId),
                'api_mode' => $this->getApiMode($modelId),
                'is_reasoning_model' => $this->isReasoningModel($modelId),
                'preset' => $this->getPresetForModel($modelId),
            ];
        }

        return $metadata;
    }
}
