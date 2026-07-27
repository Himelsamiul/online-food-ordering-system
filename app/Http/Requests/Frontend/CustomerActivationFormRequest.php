<?php

namespace App\Http\Requests\Frontend;

use App\Models\Registration;
use App\Rules\RegisteredAccountEmail;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A customer asking for their deactivated account to be switched back on.
 */
class CustomerActivationFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'email'  => [
                'required', 'email', 'max:255',
                new RegisteredAccountEmail(RegisteredAccountEmail::CUSTOMER, mustBeInactive: true),
            ],
            'phone'  => ['nullable', 'string', 'max:40'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email'  => 'registered email address',
            'reason' => 'reason for reactivation',
        ];
    }

    public function account(): ?Registration
    {
        return Registration::where('email', $this->input('email'))->first();
    }
}
