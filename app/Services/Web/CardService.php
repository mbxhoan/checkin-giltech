<?php

namespace App\Services\Web;

use App\Helpers\Helper;
use App\Models\Card;
use App\Models\Client;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CardService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Card::class);
    }

    public function getFileNameByCardTemplate(Card $card, Client $client)
    {
        $datas = $client->getFullFields();
        unset($datas['avatar']);
        unset($datas['img_qrcode']);

        return Helper::generateQrcodeByTemplate($card->file_name_template, $datas);
    }

    public function getGeneratedCardPath(Card $card, Client $client): string
    {
        $clientType = !empty($card->client_type) ? $card->client_type : $card->event_code;
        $clientType = preg_replace('/[\/\\\\\.,]+/', '_', $clientType);

        $fileName = Helper::removeSpecialCharacters($client->qrcode);
        if (!empty($card->file_name_template)) {
            $fileName = $this->getFileNameByCardTemplate($card, $client);
        }

        return "img/{$card->event_code}/{$clientType}/{$fileName}.{$card->extension}";
    }

    public function hasGeneratedCardFile(Card $card, Client $client): bool
    {
        $filePath = $this->getGeneratedCardPath($card, $client);
        return !empty($client->document_pdf)
            && $client->document_pdf === $filePath
            && Storage::exists("public/{$filePath}");
    }
}
