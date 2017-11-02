<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @SWG\Swagger(
 *     schemes={"http"},
 *     basePath="/",
 *   @SWG\Info(
 *     title="Notify Module",
 *     version="1.0.0",
 *      @SWG\Contact(
 *             email="pvalente@onesource.pt"
 *         ),
 *   )
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}