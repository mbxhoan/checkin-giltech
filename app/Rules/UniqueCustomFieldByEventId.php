<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueCustomFieldByEventId implements Rule
{
    protected $field;
    protected $eventId;
    protected $excludeClientId;

    /**
     * @param string $field The custom_fields key to check
     * @param int $eventId The current event ID to scope uniqueness
     * @param int|null $excludeClientId Optional: ID to exclude on update
     */
    public function __construct($field, $eventId, $excludeClientId = null)
    {
        $this->field = $field;
        $this->eventId = $eventId;
        $this->excludeClientId = $excludeClientId;
    }

    public function passes($attribute, $value)
    {
        $query = DB::table('clients')
            ->where("custom_fields->{$this->field}", $value)
            ->where('event_id', $this->eventId);

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
