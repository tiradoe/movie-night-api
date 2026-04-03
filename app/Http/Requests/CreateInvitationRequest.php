<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CreateInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'emails' => 'required|array',
            'emails.*' => [
                'required',
                'email',
                // They haven't been invited to the movie list
                Rule::unique('invitations', 'email')
                    ->where('movie_list_id', $this->input('movie_list_id')),
                // The user isn't already a collaborator
                Rule::notIn(
                    DB::table('movie_list_user')
                        ->join('users', 'users.id', '=', 'movie_list_user.user_id')
                        ->where('movie_list_id', $this->input('movie_list_id'))
                        ->pluck('users.email')
                        ->push($this->user()->email)
                        ->toArray()
                ),
            ],
            'movie_list_id' => 'required|exists:movie_lists,id',
        ];
    }

    public function messages()
    {
        return [
            'emails.*.unique' => 'The email address is already invited to this movie list.',
            'emails.*.not_in' => ':input is already a collaborator.',
        ];
    }
}
