<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueCustomField implements Rule
{
    protected $field;
    protected $excludeClientId;

    public function __construct($field, $excludeClientId = null)
    {
        $this->field = $field;
        $this->excludeClientId = $excludeClientId;
    }

    public function passes($attribute, $value)
    {
        $query = DB::table('clients')
            /* không biết hiểu không */
            ->where("custom_fields->{$this->field}", $value);

        if ($this->excludeClientId) {
            $query->where('id', '!=', $this->excludeClientId);
        }

        return !$query->exists();
    }

    public function message()
    {
        return "Trường :attribute đã tồn tại.";
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    /* public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
    } */
}
