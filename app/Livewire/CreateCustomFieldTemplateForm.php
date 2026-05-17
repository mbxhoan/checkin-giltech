<?php

namespace App\Livewire;

use App\Http\Requests\Admin\CustomFieldTemplates\CreateRequest;
use App\Models\CustomFieldTemplate;
use Livewire\Component;

class CreateCustomFieldTemplateForm extends Component
{
    public function createTemplate(CreateRequest $request)
    {
        $attributes = $request->only(['new'])['new'];
        CustomFieldTemplate::create([
            'event_id'      => (int)$attributes['event_id'],
            'order'         => (int)$attributes['order'],
            'name'          => $attributes['name'],
            'description'   => $attributes['description'],
            'type'          => $attributes['type'],
        ]);

        session()->flash('message', 'Tạo mới trường thông tin thành công');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.create-custom-field-template-form');
    }
}
