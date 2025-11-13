<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RankRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Asumimos que el middleware de la ruta ya verificó si es admin
        return true; 
    }

    public function rules(): array
    {
        $rankId = $this->route('rank')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ranks')->ignore($rankId),
            ],
            'required_referrals' => ['required', 'integer', 'min:0'],
            'reward_description' => ['nullable', 'string', 'max:500'],
            'reward_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}