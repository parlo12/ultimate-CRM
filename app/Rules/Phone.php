<?php

    namespace App\Rules;

    use Closure;
    use Illuminate\Contracts\Validation\ValidationRule;

    class Phone implements ValidationRule
    {

        protected string $value;

        /**
         * Create a new rule instance.
         *
         * @param $value
         */
        public function __construct($value)
        {
            $this->value = $value;
        }

        /**
         *
         * @param string  $attribute
         * @param mixed   $value
         * @param Closure $fail
         * @return void
         */
        public function validate(string $attribute, mixed $value, Closure $fail): void
        {

            $checkNumeric = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-. \\\/]?)?((?:\(?\d+\)?[\-. \\\/]?)*)(?:[\-. \\\/]?(?:#|ext\.?|extension|x)[\-. \\\/]?(\d+))?$%i', $value) && strlen($value) >= 7 && strlen($value) <= 17;

            if ( ! $checkNumeric) {
                $fail(__('locale.customer.invalid_phone_number', ['phone' => $this->value]));
            }
        }

    }
