<?php
namespace App\Services\Admin;

use App\Helpers\Helper;
use App\Services\Middleware\CardService as MiddlewareCardService;
use App\Services\BaseService;
use App\Models\Card;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;

class CardService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Card::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function card_detail()
    {
        return app(CardDetailService::class);
    }

    public function imp_exp_file()
    {
        return app(ImpexpFileService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function middleware_card()
    {
        return app(MiddlewareCardService::class);
    }

    public function generate($cardId, $clientId = null)
    {
        return $this->middleware_card()->generate($cardId, $clientId);
    }

    public function getGenerateFilesCount(Card $card)
    {
        $generatedFiles = Storage::disk('local')->files("public/img/{$card->event->code}/".($card->client_type ?? $card->event->code));
        return count($generatedFiles);
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
