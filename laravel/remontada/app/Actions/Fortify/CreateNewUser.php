<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Business;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'business_name' => ['nullable', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        // Create the user
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Create a default business and assign owner role
        $businessName = $input['business_name'] ?? ($input['name'] . "'s Business");
        $business = Business::create([
            'name' => $businessName,
            'description' => 'Owner-created business',
        ]);

        // Set current business
        $user->current_business_id = $business->id;
        $user->save();

        // Attach pemilik role for this business
        $ownerRole = Role::where('name', 'pemilik')->first();
        if ($ownerRole) {
            $user->businesses()->attach($business->id, ['role_id' => $ownerRole->id]);
        }

        return $user;
    }
}
