<?php

namespace App\Services\Admin;

use App\Models\WebhookPostmark;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class PostmarkService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(WebhookPostmark::class);
    }

    public function countByStatus(array $messageIds)
    {
        $statuses = [];
        $messages = $this->getMessages($messageIds);

        foreach ($messages as $detail) {
            if (in_array($detail->status, [
                "SubscriptionChange"
            ])) continue;

            if (!isset($statuses[$detail->status])) $statuses[$detail->status] = 0;
            $statuses[$detail->status] += 1;
        }

        return $statuses;
    }

    public function getMessages(?array $messageIds = [], ?string $selectRaw = null, ?array $customGroupBy = [], ?array $attributes = [])
    {
        $query = DB::table('webhook_postmarks');

        if (count($messageIds)) {
            $query->whereIn('message_id', $messageIds);
        }

        if (empty($selectRaw)) {
            $selectRaw =
            '
            webhook_postmarks.email,
            webhook_postmarks.status,
            count(*) as total_webhook';
        }

        if (!count($customGroupBy)) {
            $customGroupBy = [
                // 'webhook_postmarks.message_id',
                'webhook_postmarks.email',
                'webhook_postmarks.status',
            ];
        }

        $query->selectRaw($selectRaw);

        if (count($attributes)) {
            foreach ($attributes as $attrCol => $attrValue) {
                if (is_array($attrValue)) {
                   $query->whereIn($attrCol, $attrValue);
                } else {
                   $query->where($attrCol, $attrValue);
                }
            }
        }

        return $query->groupBy($customGroupBy)
            ->orderBy('total_webhook', 'DESC')
            ->get();
    }
}
