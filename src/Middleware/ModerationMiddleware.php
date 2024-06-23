<?php

namespace Msc\ChatGPT\Middleware;

use Flarum\Api\JsonApiResponse;
use Flarum\Http\UrlGenerator;
use Laminas\Diactoros\Uri;
use Msc\ChatGPT\Agent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;

class ModerationMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected Agent $agent
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $title = $request->getParsedBody()['data']['attributes']['title'];
            $firstPost = $request->getParsedBody()['data']['attributes']['content'];

            if ($this->agent->checkModeration($title, $firstPost)) {
                $error = new ResponseBag('422', [
                    [
                        'status' => '422',
                        'code' => 'validation_error',
                        'source' => [
                            'pointer' => '/data/attributes/content',
                        ],
                        'detail' => 'Your post includes bad words. Please try again.',
                    ],
                ]);

                $document = new Document();
                $document->setErrors($error->getErrors());

                return new JsonApiResponse($document, $error->getStatus());
            }
        }
        return $handler->handle($request);
    }
}
