<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maarheeze\Compass\Collector\QueueNames;
use Maarheeze\Compass\Collector\QueuePriority;
use Maarheeze\Compass\Collector\Runs\InspectRequestingSite;

readonly class ClientController
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
        private QueueNames $queueNames,
        private ResponseFactory $responseFactory,
    ) {
    }

    public function store(Request $request): Response
    {
        $key = $request->bearerToken();

        if ($key !== null) {
            $inspectRequestingSite = new InspectRequestingSite($key);
            $inspectRequestingSite->onQueue($this->queueNames->forPriority(QueuePriority::HIGH));
            $this->dispatcher->dispatch($inspectRequestingSite);
        }

        return $this->responseFactory->noContent(Response::HTTP_ACCEPTED);
    }
}
