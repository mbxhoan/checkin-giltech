<?php
namespace App\Services\Api;

use App\Models\Client;
use App\Services\BaseService;

class ClientUpserter extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Client::class);
    }

    public function upsert(array $p): Client
    {
        return Client::updateOrCreate(
            ['email' => $p['email']],
            [
                'name'            => $p['full_name'] ?? null,
                'phone'           => $p['phone'] ?? null,
                'address'         => $p['address'] ?? null,
                'job'             => $p['job'] ?? null,
                'dob'             => $p['dob'] ?? null,
                'workplace'       => $p['workplace'] ?? null,
                'invoice_flag'    => $p['invoice']['flag'] ?? null,
                'invoice_name'    => $p['invoice']['name'] ?? null,
                'invoice_address' => $p['invoice']['address'] ?? null,
                'invoice_tax'     => $p['invoice']['tax'] ?? null,
                'last_order_id'   => $p['order']['id'] ?? null,
                'last_order_sum'  => $p['order']['total'] ?? null,
            ]
        );
    }
}
