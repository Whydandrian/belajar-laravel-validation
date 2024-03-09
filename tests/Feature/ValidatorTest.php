<?php

namespace Tests\Feature;

use App\Rules\RegistrationRule;
use App\Rules\Uppercase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as ValidationValidator;
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

   public function testValidatorValidData()
   {
      $data = [
         'username' => 'wah',
         'password' => 'rahasia',
         'admin' => true,
         'others' => false
      ];
      $rules = [
         'username' => 'required|email|max:100',
         'password' => 'required|min:6|max:20'
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);

      try {
         $valid = $validator->validate();
         Log::info(json_encode($valid, JSON_PRETTY_PRINT));
      } catch (ValidationException $exception) {
         self::assertNotNull($exception->validator);
         $message = $exception->validator->errors();
         Log::error($message->toJson(JSON_PRETTY_PRINT));
      }
   }

   public function testValidatorSetLocalization()
   {
      App::setLocale('id');
      $data = [
         'username' => 'wah',
         'password' => 'rahasia',
         'admin' => true,
         'others' => false
      ];
      $rules = [
         'username' => 'required|email|max:100',
         'password' => 'required|min:6|max:20'
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);

      try {
         $valid = $validator->validate();
         Log::info(json_encode($valid, JSON_PRETTY_PRINT));
      } catch (ValidationException $exception) {
         self::assertNotNull($exception->validator);
         $message = $exception->validator->errors();
         Log::error($message->toJson(JSON_PRETTY_PRINT));
      }
   }

   public function testValidatorInlineMessage()
   {
      $data = [
         'username' => 'admin',
         'password' => 'admin'
      ];
      $rules = [
         'username' => 'required|email|max:100',
         'password' => ["required", "min:6", "max:20"]
      ];

      $messages = [
         'required' => ':attribute harus diisi.',
         'email' => ':attribute harus berupa email.',
         'min' => ':attribute minimal :min karakter.',
         'max' => ':attribute maksimal :min karakter.'
      ];

      $validator = Validator::make($data, $rules, $messages);
      self::assertNotNull($validator);

      self::assertFalse($validator->passes());
      self::assertTrue($validator->fails());

      $message = $validator->getMessageBag();
      Log::info($message->toJson(JSON_PRETTY_PRINT));
   }

   public function testValidatorAdditionalValidation()
   {
      $data = [
         'username' => 'admin@email.id',
         'password' => 'admin@email.id'
      ];
      $rules = [
         'username' => 'required|email|max:100',
         'password' => ["required", "min:6", "max:20"]
      ];

      $validator = Validator::make($data, $rules);
      $validator->after(function (ValidationValidator $validationValidator) {
         $data = $validationValidator->getData();
         if ($data['username'] == $data['password']) {
            $validationValidator->errors()
               ->add('password', 'Password tidak boleh sama dengan username');
         }
      });
      self::assertNotNull($validator);

      self::assertFalse($validator->passes());
      self::assertTrue($validator->fails());

      $message = $validator->getMessageBag();
      Log::info($message->toJson(JSON_PRETTY_PRINT));
   }

   public function testValidatorCustomRule()
   {
      $data = [
         'username' => 'admin@email.id',
         'password' => 'admin@email.id'
      ];
      $rules = [
         'username' => ['required', 'email', 'max:100', new Uppercase()],
         'password' => ["required", "min:6", "max:20", new RegistrationRule()]
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);

      self::assertFalse($validator->passes());
      self::assertTrue($validator->fails());

      $message = $validator->getMessageBag();
      Log::info($message->toJson(JSON_PRETTY_PRINT));
   }
   public function testValidatorCustomFunctionRule()
   {
      $data = [
         'username' => 'admin@email.id',
         'password' => 'admin@email.id'
      ];
      $rules = [
         'username' => ['required', 'email', 'max:100', function (string $attribute, string $value, \Closure $fail) {
            if (strtoupper($value) != $value) {
               $fail("The $attribute must be UPPERCASE");
            }
         }],
         'password' => ["required", "min:6", "max:20", new RegistrationRule()]
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);

      self::assertFalse($validator->passes());
      self::assertTrue($validator->fails());

      $message = $validator->getMessageBag();
      Log::info($message->toJson(JSON_PRETTY_PRINT));
   }

   public function testValidatorRuleClasses()
   {
      $data = [
         'username' => 'wahyudi',
         'password' => 'admin123@email.id'
      ];
      $rules = [
         'username' => ['required', new In(['wahyudi', 'budi', 'andrian'])],
         'password' => ["required", Password::min(6)->letters()->numbers()->symbols()]
      ];

      $validator = Validator::make($data, $rules);
      self::assertNotNull($validator);

      self::assertTrue($validator->passes());

      $message = $validator->getMessageBag();
      Log::info($message->toJson(JSON_PRETTY_PRINT));
   }

   public function testValidatorNestedArray()
   {
      $data = [
         'name' => [
            'first' => 'wahyudi',
            'last' => 'andrian'
         ],
         'address' => [
            'street' => 'Jl. Mahoni',
            'city' => 'Jember',
            'country' => 'Indonesia',
         ]
      ];
      $rules = [
         'name.first' => ['required', 'max:100'],
         'name.last' => ['max:100'],
         'address.street' => ["max:200"],
         'address.city' => ["required", 'max:100'],
         'address.country' => ["required", 'max:100'],
      ];

      $validator = Validator::make($data, $rules);
      self::assertTrue($validator->passes());
   }
}
