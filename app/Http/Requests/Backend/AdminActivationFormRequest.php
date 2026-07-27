<?php

namespace App\Http\Requests\Backend;

use App\Models\User;
use App\Rules\RegisteredAccountEmail;
use Illuminate\Foundation\Http\FormRequest;

/**
 * "Your account has been deactivated → Request account activation."
 */
class AdminActivationFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email'    => mb_strtolower(trim((string) $this->input('email'))),
            'username' => trim((string) $this->input('username')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email'    => [
                'required', 'email', 'max:255',
                // Must be a real admin account that is actually switched off.
                new RegisteredAccountEmail(RegisteredAccountEmail::ADMIN, mustBeInactive: true),
            ],
            'reason'   => ['required', 'string', 'min:10', 'max:1000'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email'  => 'registered email address',
            'reason' => 'reason for activation',
        ];
    }

    public function account(): ?User
    {
        return User::where('email', $this->input('email'))->where('is_admin', true)->first();
    }
}
