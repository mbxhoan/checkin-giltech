<?php
namespace App\Services\Middleware;

use App\Helpers\Helper;
use App\HttpClient\HttpClient;
use App\Services\BaseService;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;

class EmailTemplateService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(EmailTemplate::class);
        $this->httpClient = new HttpClient(env("POSTMARK_API_URL"), [
            "Accept"                    => "application/json",
            "X-Postmark-Server-Token"   => env("POSTMARK_TOKEN"),
        ]);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function middleware_client()
    {
        // return app(MiddlewareClientService::class);
    }

    public function getAuthorizedPostmarkTemplateIds()
    {
        if (!auth()->user()->isSysAdmin()) {
            $postmarkTemplateIds = auth()->user()->company->templates;
            if ($postmarkTemplateIds) $postmarkTemplateIds = json_decode($postmarkTemplateIds, true);
        }

        return $postmarkTemplateIds ?? [];
    }

    public function getAuthorizedPostmarkTemplates(array $templates = [])
    {
        $authorizedPostmarkTemplateIds = $this->getAuthorizedPostmarkTemplateIds();

        if (count($authorizedPostmarkTemplateIds)) {
            if (!empty($templates) && count($templates)) {
                foreach ($templates as $index => $template) {
                    if (!isset($template['TemplateId']) || !in_array($template['TemplateId'], $authorizedPostmarkTemplateIds)) {
                        unset($templates[$index]);
                    }
                }
            }
        } else {
            if (!auth()->user()->isSysAdmin()) {
                return [];
            }
        }

        return $templates;
    }

    public function getPostmarkTemplates(bool $resync = false)
    {
        if (!$resync) {
            $templates = $this->getRedis("postmark", "email_templates", "array");

            if (count($templates)) {
                /* authorize templates */
                $templates = $this->getAuthorizedPostmarkTemplates($templates);
                return [
                    'Templates'     => $templates,
                    'TotalCount'    => count($templates,)
                ];
            }
        }

        try {
            $params = [
                'count'             => 100,
                'offset'            => 0,
                'LayoutTemplate'    => null,
                'TemplateType'      => "Standard", // Layout là layouts, Standard là templates
            ];

            $result = $this->httpClient->get("templates", $params);

            if ($result && (isset($result['TotalCount']) && isset($result['Templates']))) {
                foreach ($result['Templates'] as $index => $template) {
                    $postMarkTemplate = $this->getPostmarkTemplate($template['TemplateId']);
                    $result['Templates'][$index] = $postMarkTemplate;
                }

                /* authorize templates */
                $templates = $this->getAuthorizedPostmarkTemplates($result['Templates']);
                $result['Templates'] = $templates;
                $result['TotalCount'] = count($templates);

                /* re-assign */
                $this->updateRedis("postmark", "email_templates", json_encode($result['Templates']), config("app.times.minutes.30"));
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Get Templates: {$e}");
        }

        return [];
    }

    public function getPostmarkTemplate(int $templateId, bool $updateToRedis = false)
    {
        if (!auth()->user()->isSysAdmin()) {
            $authorizedPostmarkTemplateIds = $this->getAuthorizedPostmarkTemplateIds();

            if (!count($authorizedPostmarkTemplateIds) || !in_array($templateId, $authorizedPostmarkTemplateIds)) {
                return [];
            }
        }

        $template = $this->getRedis("postmark", "email_template-{$templateId}", "array");

        if (!$updateToRedis) {
            if (count($template) && isset($template['HtmlBody'])) {
                return $template;
            }
        }

        try {
            $result = $this->httpClient->get("templates/{$templateId}");

            if (isset($result['LayoutTemplate'])) {
                $layout = $result['LayoutTemplate'];
                $postmarkLayout = $this->httpClient->get("templates/{$layout}");

                // Replace {{{ @content }}} with child content
                $result['FullHtmlBody'] = str_replace('{{{ @content }}}', $result['HtmlBody'], $postmarkLayout['HtmlBody']);
            }

            if ($result && (isset($result['HtmlBody']))) {
                $result['placeholders'] = Helper::getPlaceholdersForPostmark([
                    $result['HtmlBody'],
                    $result['Subject']
                ]);
                $this->updateRedis("postmark", "email_template-{$templateId}", json_encode($result), config("app.times.minutes.30"));
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Get Template: {$e}");
        }

        return [];
    }

    public function updatePostmarkTemplate(int $templateId, array $attributes)
    {
        if (!auth()->user()->isSysAdmin()) {
            $authorizedPostmarkTemplateIds = $this->getAuthorizedPostmarkTemplateIds();

            if (!count($authorizedPostmarkTemplateIds) || !in_array($templateId, $authorizedPostmarkTemplateIds)) {
                return [];
            }
        }

        try {
            $datas = [
                "Name"      => $attributes['name'],
                "Subject"   => $attributes['subject'],
                "TextBody"  => Helper::convertHtmlToPlainText($attributes['html_body']),
                "HtmlBody"  => $attributes['html_body'],
                "Alias"     => $attributes['alias'] ?? null
            ];

            $result = $this->httpClient->post("templates/{$templateId}", array_filter($datas), "put");

            if ($result && isset($result['Name'])) {
                $this->getPostmarkTemplate($templateId, true);
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Update Template: {$e}");
        }

        return [];
    }

    public function sendTestPostmarkTemplate(int $templateId, array $attributes)
    {
        if (!auth()->user()->isSysAdmin()) {
            $authorizedPostmarkTemplateIds = $this->getAuthorizedPostmarkTemplateIds();

            if (!count($authorizedPostmarkTemplateIds) || !in_array($templateId, $authorizedPostmarkTemplateIds)) {
                return [];
            }
        }

        try {
            $datas = [
                "From"          => $attributes['from_mail'],
                "To"            => $attributes['to_mail'],
                "TemplateId"    => $templateId,
                "TemplateModel" => $attributes['fields'],
                "InlineCss"     => true,
                "Cc"            => $attributes['cc'],
                "Bcc"           => $attributes['bcc'],
                "Tag"           => "TEST-{$templateId}",
                "ReplyTo"       => $attributes['from_mail'],
                "TrackOpens"    => true,
                "TrackLinks"    => "None",
                "Metadata"      => [
                    "color"     => "blue",
                    "client-id" => "12345"
                ],
                "MessageStream" => "outbound"
            ];

            $result = $this->httpClient->post("email/withTemplate", $datas);

            if ($result && (isset($result['Message']) && $result['Message'] == "OK")) {
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Send Test with Template: {$e}");
        }

        return [];
    }
}
