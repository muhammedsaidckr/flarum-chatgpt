<?php

namespace Msc\ChatGPT;

interface OpenAIClientInterface
{
    public function chat(): ChatInterface;
    public function moderations(): ModerationsInterface;
    public function responses(): ResponsesInterface;
}
