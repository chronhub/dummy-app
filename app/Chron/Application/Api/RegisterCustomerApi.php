<?php

declare(strict_types=1);

namespace App\Chron\Application\Api;

use App\Chron\Application\Service\CustomerService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

final class RegisterCustomerApi
{
    public function __invoke(Request $request, CustomerService $customerService): JsonResponse
    {
        $customerService->registerCustomer($request->json()->all());

        return new JsonResponse([
            'message' => 'Customer registered successfully',
        ]);
    }
}
