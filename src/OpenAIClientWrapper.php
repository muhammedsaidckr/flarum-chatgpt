<?php

namespace Msc\ChatGPT;

use OpenAI\Client as OpenAIClient;

class OpenAIClientWrapper implements OpenAIClientInterface
{
    public function __construct(protected OpenAIClient $client)
    {
    }

    public function chat(): ChatInterface
    {
        return new ChatWrapper($this->client->chat());
    }

    public function moderations(): ModerationsInterface
    {
        return new ModerationsWrapper($this->client->moderations());
    }

    public function responses(): ResponsesInterface
    {
        return new ResponsesWrapper($this->client->responses());
    }
}

class ChatWrapper implements ChatInterface
{
    public function __construct(protected $client)
    {
    }

    public function create(array $params)
    {
        return $this->client->create($params);
    }
}

class ModerationsWrapper implements ModerationsInterface
{
    public function __construct(protected $client)
    {
    }

    public function create(array $params)
    {
        return $this->client->create($params);
    }
}

class ResponsesWrapper implements ResponsesInterface
{
    public function __construct(protected $client)
    {
    }

    public function create(array $params)
    {
        return $this->client->create($params);
    }
}
