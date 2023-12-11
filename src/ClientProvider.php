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
        $organisation = $settings->get('muhammedsaidckr-chatgpt.openai-api-organisation');

        if ($apiKey) {
            $this->container->singleton(Client::class, fn() => OpenAI::client($apiKey));
        }

        /** @var ExtensionManager $extensions */
        $extensions = $this->container->make(ExtensionManager::class);

        $this->container->singleton(Agent::class, fn() => $this->getAgent($settings, $extensions));
    }

    protected function getAgent(SettingsRepositoryInterface $settings, ExtensionManager $extensions): Agent
    {
        $userId = $settings->get('muhammedsaidckr-chatgpt.user_prompt') ?? 'flarum';

        /** @var \Flarum\User\UserRepository $users */
        $users = $this->container->make(UserRepository::class);
        $user = $users->findOrFail($userId);

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

//        $agent->toggleMentioning($extensions->isEnabled('flarum-mentions'));

        return $agent;
    }

}
