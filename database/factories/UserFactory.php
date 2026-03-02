<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => \App\Models\Role::where('name', 'admin')->first()?->id ?? 1,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => []);
    }

    /**
     * Set the user role to student.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => \App\Models\Role::where('name', 'student')->first()?->id,
        ]);
    }

    /**
     * Set the user role to cashier.
     */
    public function cashier(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => \App\Models\Role::where('name', 'cashier')->first()?->id,
        ]);
    }
}
