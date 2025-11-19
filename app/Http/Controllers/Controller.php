<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="0.1.0",
 *      title="Art Auction API",
 *      description="Backend for React & Flutter clients",
 *      @OA\Contact(email="team@example.com")
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="apiKey",
 *      in="header",
 *      name="Authorization",
 *      description="Use format:  Bearer {token}"
 * )
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
