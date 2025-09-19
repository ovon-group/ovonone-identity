<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class MyAuthorizationController extends AuthorizationController
{
//    public function authorize(
//        ServerRequestInterface $psrRequest,
//        Request $request,
//        ResponseInterface $psrResponse,
//        AuthorizationViewResponse $viewResponse
//    ): Response|AuthorizationViewResponse
//    {
//        dd($request, $this);
//    }
}
