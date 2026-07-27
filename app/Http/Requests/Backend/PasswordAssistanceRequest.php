<?php

namespace App\Http\Requests\Backend;

use App\Models\User;
use App\Rules\RegisteredAccountEmail;
use Illuminate\Foundation\Http\FormRequest;

/**
 * "Not a Super Admin? Request password assistance."
 *
 * Public — the whole point is that the admin cannot sign in.
 */
class PasswordAssistanceRequest extends FormRequest
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
                // Must be a real admin account, and one that is switched on —
                // a deactivated admin needs the activation form, not this one.
                new RegisteredAccountEmail(RegisteredAccountEmail::ADMIN, mustBeInactive: false),
            ],
            'role'     => ['nullable', 'string', 'max:60'],
            'reason'   => ['required', 'string', 'min:10', 'max:1000'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email'  => 'registered email address',
            'reason' => 'reason for request',
            'notes'  => 'additional notes',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.min' => 'Please give the super admin enough detail to act on — at least 10 characters.',
        ];
    }

    /** The account the form names, resolved once. */
    public function account(): ?User
    {
        return User::where('email', $this->input('email'))->where('is_admin', true)->first();
    }
}
