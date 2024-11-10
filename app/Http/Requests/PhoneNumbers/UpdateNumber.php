<?php

namespace App\Http\Requests\PhoneNumbers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNumber extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit phone_numbers');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        $id = $this->route('phone_number');
        $number = $this->number;

        return [
            'number' => ['required',
                Rule::unique('phone_numbers')->where(function ($query) use ($number) {
                    return $query->where('number', $number);
                })->ignore($id, 'uid')],
            'user_id' => 'required|integer',
            'price' => 'required|min:0|max:12',
            'status' => 'required|string',
            'capabilities' => 'required|array|min:1',
            'capabilities.*' => 'required|string|distinct|min:3',
            'currency_id' => 'required',
            'billing_cycle' => 'required|string',
            'frequency_amount' => 'required_if:billing_cycle,=,custom|nullable|numeric',
            'frequency_unit' => 'required_if:billing_cycle,=,custom|nullable|string',
            'sending_server_id' => 'required|integer',

        ];
    }

    /**
     * custom message
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'number.unique' => __('locale.phone_numbers.number_available', ['number' => $this->number]),
        ];
    }
}
