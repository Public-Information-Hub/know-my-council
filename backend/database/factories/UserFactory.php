<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'display_name' => $name,
            'handle' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'public_bio' => fake()->optional()->sentence(),
            'account_state' => 'active',
            'verification_level' => 'verified',
            'trust_level' => 'trusted',
            'is_super_admin' => false,
            'two_factor_mode' => 'email_code',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'last_seen_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'account_state' => 'pending',
            'verification_level' => 'unverified',
            'trust_level' => 'untrusted',
            'is_super_admin' => false,
        ]);
    }
}
