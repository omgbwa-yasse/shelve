<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		if (!is_string($value)) {
			return false;
		}

		// At least 8 characters
		if (mb_strlen($value) < 8) {
			return false;
		}

		// At least one lowercase letter
		if (!preg_match('/[a-z]/', $value)) {
			return false;
		}

		// At least one uppercase letter
		if (!preg_match('/[A-Z]/', $value)) {
			return false;
		}

		// At least one digit
		if (!preg_match('/[0-9]/', $value)) {
			return false;
		}

		// At least one special character
		if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
			return false;
		}

		return true;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return __('The :attribute must be at least 8 characters and contain upper and lower case letters, a number and a special character.');
	}
}

