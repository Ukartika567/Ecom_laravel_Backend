<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\Info(
 *    title="Your super  ApplicationAPI",
 *    version="1.0.0",
 * )
 * * @OA\post(
 *     path="/api/login",
 *     @OA\Response(response="200", description="An example endpoint")
 * )
 * @OA\get(
*    path="/api/subcatdetails",
 *   @OA\Response(response="200", description="An example endpoint")
*)
 *
 * @OA\get(
 *     path="/api/subcat",
 *     @OA\Response(response="200", description="An example endpoint")
 * )
 * @OA\PathItem (
 *  path="/api/users",
 *     @OA\Response(response="200", description="An example endpoint")),
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
