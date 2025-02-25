<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ErrorResourceCollection extends ResourceCollection
{
    private $message;
    private $statusCode;

    public function __construct($resource, $message = 'An error occurred', $statusCode = 500)
    {
        parent::__construct($resource);
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    public function toArray($request)
    {
        return [
            'success' => false,
            'message' => $this->message,
            'errors' => $this->collection,
            'status' => $this->statusCode,
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->statusCode);
    }
}
