<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueCustomFieldByAttributes implements Rule
{
    protected $field;
    protected $attributes;
    protected $excludeClientId;

    /**
     * @param string $field The custom_fields key to check
     * @param int $attributes The current event ID to scope uniqueness
     * @param int|null $excludeClientId Optional: ID to exclude on update
     */
    public function __construct($field, array $attributes = [], $excludeClientId = null)
    {
        $this->field = $field;
        $this->attributes = $attributes;
        $this->excludeClientId = $excludeClientId;
    }

    public function passes($attribute, $value)
    {
        $query = DB::table('clients')
            ->where("custom_fields->{$this->field}", $value);

        if (count($this->attributes)) {
            foreach ($this->attributes as $key => $val) {
                $query->where($key, $val);
            }
        }

        if ($this->excludeClientId) {
            $query->where('id', '!=', $this->excludeClientId);
        }

        return !$query->exists();
    }

    public function message()
    {
        return "Trường :attribute đã tồn tại.";
    }
}
