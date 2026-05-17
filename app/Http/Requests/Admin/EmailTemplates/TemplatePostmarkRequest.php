<?php

namespace App\Http\Requests\Admin\EmailTemplates;

use App\Rules\WithoutSpace;
use Illuminate\Foundation\Http\FormRequest;

class TemplatePostmarkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'alias' => [
                'nullable',
                'string',
                'max:55',
                new WithoutSpace
            ],
            'template_id' => [
                'required',
                'numeric',
            ],
            // 'text_body' => [
            //     'required',
            // ],
            'html_body' => [
                'required'
            ],
            'subject' => [
                'required'
            ]
        ];
    }

    public function attributes()
    {
        return [
            'name'          => 'Tên',
            'alias'         => 'Alias',
            'template_id'   => 'Template ID',
            'text_body'     => 'Text',
            'html_body'     => 'Html',
            'subject'       => 'Tiêu đề',
        ];
    }
}
