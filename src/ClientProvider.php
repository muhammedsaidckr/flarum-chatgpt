<?php

namespace Msc\ChatGPT;

use Flarum\Extension\ExtensionManager;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Container\Container;
use OpenAI;
use OpenAI\Client;

class ClientProvider extends AbstractServiceProvider
{
    public function register()
    {
    }

    public function boot(Container $container)
    {
        /** @var \Flarum\Settings\SettingsRepositoryInterface $settings */
        $settings = $this->container->make(SettingsRepositoryInterface::class);

        $apiKey = $settings->get('muhammedsaidckr-chatgpt.api_key');

        if ($apiKey) {
            $this->container->singleton(Client::class, fn() => $this->getClient($settings));
        }

        /** @var ExtensionManager $extensions */
        $extensions = $this->container->make(ExtensionManager::class);

        $this->container->singleton(Agent::class, fn() => $this->getAgent($settings, $extensions));
    }

    protected function getAgent(SettingsRepositoryInterface $settings, ExtensionManager $extensions): Agent
    {
        $userId = $settings->get('muhammedsaidckr-chatgpt.user_prompt') ?? 1;

        /** @var \Flarum\User\UserRepository $users */
        $users = $this->container->make(UserRepository::class);

        // Try to find the configured user, fall back to admin (ID 1)
        $user = $users->find($userId);

        // If configured user doesn't exist, try to find admin user
        if (!$user && $userId != 1) {
            $user = $users->find(1);
        }

        // If still no user found, throw a helpful error
        if (!$user) {
            throw new \RuntimeException(
                "ChatGPT extension error: Cannot find user ID {$userId}. " .
                "Please configure a valid user ID in extension settings at 'User Prompt' field, " .
                "or ensure at least one user exists in your forum."
            );
        }

        /** @var Client $client */
        $client = $this->container->has(Client::class)
            ? $this->container->make(Client::class)
            : null;

        $agent = new Agent(
            user: $user,
            client: $client,
            model: $settings->get('muhammedsaidckr-chatgpt.model'),
            maxTokens: $settings->get('muhammedsaidckr-chatgpt.max_tokens'),
        );

        return $agent;
    }

    protected function getClient(SettingsRepositoryInterface $settings)
    {
        $apiKey = $settings->get('muhammedsaidckr-chatgpt.api_key');

        $baseUri = $settings->get('muhammedsaidckr-chatgpt.base_uri');

        return OpenAI::factory()
            ->withApiKey($apiKey)
            ->withBaseUri($baseUri)
            ->make();
    }

}
