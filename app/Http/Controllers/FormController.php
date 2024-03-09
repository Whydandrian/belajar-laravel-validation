<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{

   public function form(): Response
   {
      return response()->view('form');
   }

   public function submitForm(LoginRequest $request): Response
   {
      $data = $request->validated();
      return response('OK', Response::HTTP_OK);
   }

   public function login(Request $request): Response
   {
      try {
         $rules = [
            'username' => 'required',
            'password' => 'required'
         ];
         $data = request()->validate($rules);
         // $data manipulation
         return response('OK', Response::HTTP_OK);
      } catch (ValidationException $exception) {
         return response($exception->errors(), Response::HTTP_BAD_REQUEST);
      }
   }
}
