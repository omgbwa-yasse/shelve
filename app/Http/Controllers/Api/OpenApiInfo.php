<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'SpecKit Digital Records API',
    description: 'RESTful API for managing digital records, documents, artifacts, and periodicals',
    contact: new OA\Contact(
        name: 'SpecKit Support',
        email: 'support@speckit.com'
    )
)]
#[OA\Server(
    url: '/api/v1',
    description: 'API v1'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'token',
    description: 'Laravel Sanctum authentication token'
)]
class OpenApiInfo
{
    // This class is used only for OpenAPI documentation metadata
}
