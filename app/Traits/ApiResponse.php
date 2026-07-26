<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    public function successResponse($message = null, $code = 200, $businessCode = 'OK')
    {
        return response()->json([
            'status'  => true,
            'message' => $message ?? __('api.success'),
            'code'    => $businessCode,
        ], $code);
    }

    public function dataResponse($data = null, $message = null, $code = 200, $status = true)
    {
        return response()->json([
            'status'  => $status,
            'message' => $message ?? __('api.success'),
            'data'    => $data,
        ], $code);
    }

    public function errorResponse($error = null, $message = null, $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message ?? __('api.error'),
            'error'   => $error,
        ], $code);
    }

    public function paginatedResponse(LengthAwarePaginator $paginatedData, String|null $message = null)
    {
        return response()->json([
            'status'     => true,
            'message'    => $message ?? __('api.success'),
            'data'       => $paginatedData->items(),
            'pagination' => [
                'total'        => $paginatedData->total(),
                'current_page' => $paginatedData->currentPage(),
                'last_page'    => $paginatedData->lastPage(),
                'per_page'     => $paginatedData->perPage(),
                'from'         => $paginatedData->firstItem(),
                'to'           => $paginatedData->lastItem(),
            ]
        ], 200);
    }
}
