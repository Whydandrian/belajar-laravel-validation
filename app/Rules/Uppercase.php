<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Uppercase implements ValidationRule
{

   public function validate(string $attribute, mixed $value, Closure $fail): void
   {
      if ($value !== strtoupper($value)) {
         $fail("validation.custom.uppercase")->translate([
            'attribute' => $attribute,
            'value' => $value,
         ]);
      }
   }
}
