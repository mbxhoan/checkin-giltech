<?php

namespace App\Http\Requests\Admin\LandingPages;

use Illuminate\Foundation\Http\FormRequest;

class CloneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user->isSysAdmin()) {
            return true;
        }

        return $user->authorizeSelfByEventId($this->event_id);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'event_id'      => 'required|integer|exists:events,id',
            'name'          => [
                'required',
                'regex:/^[a-z0-9-_]+$/', // only lowercase, numbers, dashes, underscores
                'unique:landing_pages,slug',
                'max:20'
            ],
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Tên landing page nhân bản',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->service->ensureLimited($this->input('event_id'))) {
                $validator->errors()->add('event_id', 'Đã vượt quá số lượng trang cho phép');
            }
        });
    }
}
