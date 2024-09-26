<?php

namespace App\Http\Controllers\AuthApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Info(
 *     title="Kumande API Docs (Laravel)",
 *     version="1.0.0",
 *     description="API Documentation for Kumande Mobile & Web Apps",
 *     @OA\Contact(
 *         email="flazen.edu@gmail.com"
 *     )
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Authorization header using the Bearer scheme",
 * )
*/
class Queries extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/logout",
     *     summary="Sign out from Apps",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Logout success"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout success'
        ], Response::HTTP_OK);
    }
}
