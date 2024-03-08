<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

use function PHPUnit\Framework\assertNull;

class ValidatorTest extends TestCase
{
   public function testValidator()
   {
      $data = [
         'username' => 'admin',
         'password' => '12345'
      ];
      $rules = [
         'username' => 'required',
         'password' => 'required'
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);
      self::assertTrue($validator->passes());
      self::assertFalse($validator->fails());
   }

   public function testValidatorInvalid()
   {
      $data = [
         'username' => '',
         'password' => ''
      ];
      $rules = [
         'username' => 'required',
         'password' => 'required'
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);
      self::assertFalse($validator->passes());
      self::assertTrue($validator->fails());
      $message = $validator->getMessageBag();

      Log::info($message->toJson(JSON_PRETTY_PRINT));
   }
   public function testValidatorException()
   {
      $data = [
         'username' => '',
         'password' => ''
      ];
      $rules = [
         'username' => 'required',
         'password' => 'required'
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);

      try {
         $validator->validate();
         self::fail("Validation Exception Not Thrown.");
      } catch (ValidationException $exception) {
         self::assertNotNull($exception->validator);
         $message = $exception->validator->errors();
         Log::error($message->toJson(JSON_PRETTY_PRINT));
      }
   }

   public function testValidatorRules()
   {
      $data = [
         'username' => 'admin',
         'password' => 'rahasia'
      ];
      $rules = [
         'username' => 'required|email|max:100',
         'password' => ["required", "min:6", "max:20"]
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);
      $message = $validator->getMessageBag();
      Log::error($message->toJson(JSON_PRETTY_PRINT));
   }
}
