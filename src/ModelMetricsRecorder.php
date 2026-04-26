<?php

namespace Msc\ChatGPT;

class ModelMetricsRecorder
{
    public function record(array $metric): void
    {
        try {
            $db = resolve('db');

            $record = [
                'discussion_id' => $metric['discussion_id'] ?? null,
                'model' => (string) ($metric['model'] ?? 'unknown'),
                'model_family' => (string) ($metric['model_family'] ?? 'legacy'),
                'api_mode' => (string) ($metric['api_mode'] ?? 'chat_completions'),
                'status' => (string) ($metric['status'] ?? 'unknown'),
                'request_count' => 1,
                'prompt_tokens' => (int) ($metric['prompt_tokens'] ?? 0),
                'completion_tokens' => (int) ($metric['completion_tokens'] ?? 0),
                'total_tokens' => (int) ($metric['total_tokens'] ?? 0),
                'latency_ms' => (int) ($metric['latency_ms'] ?? 0),
                'estimated_cost_usd' => (float) ($metric['estimated_cost_usd'] ?? 0.0),
                'had_refusal' => (bool) ($metric['had_refusal'] ?? false),
                'error_message' => $metric['error_message'] ?? null,
                'pricing_version' => (string) ($metric['pricing_version'] ?? PricingCalculator::DEFAULT_VERSION),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $db->table('chatgpt_model_metrics')->insert($record);

            $date = date('Y-m-d');
            $dailyTable = $db->table('chatgpt_model_metrics_daily');
            $existing = $dailyTable
                ->where('metric_date', $date)
                ->where('model', $record['model'])
                ->first();

            if ($existing) {
                $dailyTable
                    ->where('metric_date', $date)
                    ->where('model', $record['model'])
                    ->update([
                        'model_family' => $record['model_family'],
                        'api_mode' => $record['api_mode'],
                        'request_count' => ((int) $existing->request_count) + 1,
                        'success_count' => ((int) $existing->success_count) + ($record['status'] === 'success' ? 1 : 0),
                        'error_count' => ((int) $existing->error_count) + ($record['status'] === 'success' ? 0 : 1),
                        'prompt_tokens' => ((int) $existing->prompt_tokens) + $record['prompt_tokens'],
                        'completion_tokens' => ((int) $existing->completion_tokens) + $record['completion_tokens'],
                        'total_tokens' => ((int) $existing->total_tokens) + $record['total_tokens'],
                        'latency_ms_total' => ((int) $existing->latency_ms_total) + $record['latency_ms'],
                        'estimated_cost_usd' => round(((float) $existing->estimated_cost_usd) + $record['estimated_cost_usd'], 8),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                return;
            }

            $dailyTable->insert([
                'metric_date' => $date,
                'model' => $record['model'],
                'model_family' => $record['model_family'],
                'api_mode' => $record['api_mode'],
                'request_count' => 1,
                'success_count' => $record['status'] === 'success' ? 1 : 0,
                'error_count' => $record['status'] === 'success' ? 0 : 1,
                'prompt_tokens' => $record['prompt_tokens'],
                'completion_tokens' => $record['completion_tokens'],
                'total_tokens' => $record['total_tokens'],
                'latency_ms_total' => $record['latency_ms'],
                'estimated_cost_usd' => $record['estimated_cost_usd'],
                'pricing_version' => $record['pricing_version'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            $log = resolve('log');
            $log->warning('[ChatGPT] Failed to record model metric', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
