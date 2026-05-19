<?php

namespace App\Helpers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Normalizer;
use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

if (!function_exists('get_last_name')) {
    function get_last_name(string $fullName): string
    {
        $nameParts = explode(' ', trim($fullName));
        return end($nameParts);
    }
}

class Helper
{
    /* customize */
    /* phi-lao-mims-811 */
    public static function nextCode(string $code, int $step = 1): string
    {
        if (!preg_match('/^([^\d]+)(\d+)$/', $code, $m)) {
            return "";
            throw new InvalidArgumentException("Code must be <prefix><number>, e.g. AST001");
        }

        [$all, $prefix, $num] = $m;
        $width = strlen($num);
        $next  = (int)$num + $step;

        return $prefix . str_pad((string)$next, $width, '0', STR_PAD_LEFT);
    }

    public static function generateCode($prefix = "UNK", $postFixLength = 8): string
    {
        return "{$prefix}-".strtoupper(Str::random($postFixLength));
    }

    public static function generateSimpleQrcode($eventCode, $postfixLength = 4)
    {
        // <Mã sự kiên><NămThángNgàyGiờPhútGiây><Chuỗi ngẫu nhiên (5 ký tự, chuỗi & số)>

        // $time = date("YmdHis");
        $time = date("His");
        $randomCode = strtoupper(self::randomCode($postfixLength, true));
        $qrcode = "{$time}{$randomCode}";
        return $qrcode;
    }

    public static function createSlug($input, $upperCase = false)
    {
        $result = Str::slug($input, '_');

        if ($upperCase) {
            $result = Str::upper($result);
        }

        return $result;

        /* $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $input);
        $snakeCase = Str::slug($normalized, '_');
        return $snakeCase; */
    }

    public static function generateInfoQrcode($eventCode, $phone, $email, $name)
    {
        // <Mã sự kiên>-<NămThángNgàyGiờPhútGiây>_<Số điện thoại>_<Email>_<Họ tên (IN HOA, không cách, không dấu)>_<Chuỗi ngẫu nhiên (3 ký tự, chuỗi & số)>

        $time = date("YmdHis");
        $randomCode = strtoupper(self::randomCode(3, true));

        if (empty($phone) || !is_numeric($phone)) {
            $phone = 0;
        }

        if (empty($name)) {
            $name = "UNKNOWN";
        } else {
            $name = self::removeSpaceOnStr(trim($name), true, true, true);
        }

        $email = trim($email);

        if (!empty($email) && self::checkEmailForm($email)) {
            $email = strtolower($email);
        } else {
            $email = "noemail@gmail.com";
        }

        $qrcode = "{$eventCode}-{$time}_{$phone}_{$email}_{$name}_{$randomCode}";
        return $qrcode;
    }

    public static function randomCode($length = 8, $noTelex = false, $includeSymbols = false)
    {
        $permittedChars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($noTelex) {
            $permittedChars = '0123456789bcghklmnpqrtvzBCGHKLMNPQRTVZ';
        }

        if ($includeSymbols) {
            $permittedChars .= '!@#$%^&*_-+=<>?.,/';
        }

        $inputLength = strlen($permittedChars);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomCharacter = $permittedChars[mt_rand(0, $inputLength - 1)];
            $randomString .= $randomCharacter;
        }

        return $randomString;
    }

    public static function containsSpecialCharacters($string, $allowUnderscore = true) {
        // Define the special characters you want to check
        $permittedChars = '!@#$%^&*-+=<>?.,/';

        if (!$allowUnderscore) {
            $permittedChars .= '_';
        }

        // Use preg_match to check if the string contains any of the special characters
        if (preg_match('/[' . preg_quote($permittedChars, '/') . ']/', $string)) {
            return false;
        }

        return true;
    }

    public static function generateQrcode($prefix, $length = 5)
    {
        $randomCode = strtoupper(self::randomCode($length, true));
        $time = date('dmyhis');
        $qrcode = "{$prefix}{$time}{$randomCode}";

        return $qrcode;
    }

    public static function generateImgQrcode(
        string $qrcode,
        string $folder = 'qrcode',
        array $config = [],
    )
    {
        $folder = strtolower($folder);
        $path = "qrcodes/{$folder}";
        $folderPath = storage_path("app/public/{$path}");
        // $folderPath = storage_path($path);

        /* CONFIG */
        $qrcodeColor = $config['qrcode_color'] ?? "#000000";
        $qrcodeBgColor = $config['qrcode_bg_color'] ?? "#ffffff";
        $qrcodeCorrection = $config['qrcode_correction'] ?? "L";
        $output = $config['output'] ?? "png";
        $logoPath = $config['logo_path'] ?? null;
        $logoWidth = $config['logo_width'] ?? .3;
        $fileName = $config['file_name'] ?? self::removeSpecialCharacters($qrcode);
        $displayText = $config['display_text'] ?? $qrcode;

        if (!File::isDirectory($folderPath)) {
            File::makeDirectory($folderPath, 0700, true, true);
        }

        $tmpPath = "{$folderPath}/{$fileName}_tmp.{$output}";
        $mainPath = "{$folderPath}/{$fileName}.{$output}";

        list($color_r, $color_g, $color_b) = sscanf($qrcodeColor, "#%02x%02x%02x");
        list($background_r, $background_g, $background_b) = sscanf($qrcodeBgColor, "#%02x%02x%02x");

        $qrcodeGenerate = QrCode::format($output)
            ->size(300)
            ->errorCorrection($qrcodeCorrection)
            ->color($color_r, $color_g, $color_b)
            ->backgroundColor($background_r, $background_g, $background_b)
            ->encoding('UTF-8');


        /* attach logo */
        if ($logoPath) {
            if (File::exists($logoPath)) {
                $qrcodeGenerate = $qrcodeGenerate->merge($logoPath, $logoWidth, true)
                                                ->errorCorrection('Q');
            }
        }

        if (!$config['white_border']) {
            $tmpPath = $mainPath;
        }

        $qrcodeGenerate = $qrcodeGenerate->generate($qrcode, $tmpPath);

        if ($config['white_border']) {
            /* Generate white border */
            $qr = Image::make($tmpPath);

            if (isset($config['with_text'] ) && $config['with_text']) {
                $border = Image::canvas(350, 370, $qrcodeBgColor);
                $border->insert($qr, 'top', 0, 30);

                /* Insert code text */
                $border->text($displayText, ($border->getWidth()/2), ($border->getHeight() - 15), function($font) use($qrcodeColor) {
                    $font->file(public_path('assets/fonts/montserrat/Montserrat-Regular.ttf')); // Replace with the path to your font file
                    $font->size(14); // Adjust the font size as needed
                    $font->color($qrcodeColor); // Adjust the font color as needed
                    $font->align('center');
                    $font->valign('bottom');
                });
            } else {
                $border = Image::canvas(350, 350, $qrcodeBgColor);
                $border->insert($qr, 'center');
            }

            $border->save($mainPath);

            /* Remove tmp Qr code */
            File::delete($tmpPath);
        }

        // return Storage::url("public/qrcodes/{$folder}/{$qrcode}.{$output}"); // app/storage/app/qr
        return "{$path}/{$fileName}.{$output}";
    }

    public static function deleteFileStorage(?string $filePath = null, $isPublic = true)
    {
        if (empty($filePath)) {
            return false;
        }

        if ($isPublic) {
            $filePath = "public/{$filePath}";
        }

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            return true;
        }

        return false;
    }

    public static function isTodayInRange(DateTimeInterface|string|null $fromDate, DateTimeInterface|string|null $toDate): bool
    {
        if (empty($fromDate) || empty($toDate)) {
            return false;
        }

        return Carbon::today()->between(
            Carbon::parse($fromDate)->startOfDay(),
            Carbon::parse($toDate)->endOfDay()
        );
    }

    public static function removeSpecialCharacters($string)
    {
        return str_replace(array('*',',','.','"','/','\\','[',']',':',';','|','?','<','>','#'), '-', $string);
    }

    public static function generateQrcodeByTemplate($template, $data, $paddingForNumerical = 5, $randomStringLength = 5)
    {
        preg_match_all('/<([^>]*)>/', $template, $matches);
        $placeholders = $matches[1];

        foreach ($placeholders as $placeholder) {
            switch ($placeholder) {
                case "numericalOrder":
                    if (!is_string($data['numericalOrder'])) $data['numericalOrder'] = "";
                    $data['numericalOrder'] = str_pad($data['numericalOrder'], $paddingForNumerical, '0', STR_PAD_LEFT);
                    break;
                case "randomString":
                    if (!is_string($data['randomString'])) $data['randomString'] = "";
                    $data['randomString'] = strtoupper(self::randomCode($randomStringLength, true));
                    break;
                case "currentTime":
                    if (!is_string($data['currentTime'])) $data['currentTime'] = "";
                    $data['currentTime'] = date("YmdHis");
                    break;
                case "name":
                    $data['name'] = Normalizer::normalize($data['name'], Normalizer::FORM_C);
                    $data['name'] = self::removeSpaceOnStr(trim($data['name']), true, true, true);
                    break;
                case "email":
                    $email = $data['email'];
                    $email = trim($email);

                    if (!empty($email) && self::checkEmailForm($email)) {
                        $email = strtolower($email);
                    } else {
                        $email = "noemail@gmail.com";
                    }

                    $data['email'] = $email;
                    break;
                default:
                    if (!isset($data[$placeholder])) $data[$placeholder] = "";
                    if (!is_string($data[$placeholder]) && !is_numeric($data[$placeholder])) $data[$placeholder] = "";
                    $data[$placeholder] = self::removeSpaceOnStr(trim($data[$placeholder]), true, true, true);
            }
        }

        $code = $template;

        foreach ($placeholders as $placeholder) {
            if (isset($data[$placeholder])) {
                $code = str_replace('<' . $placeholder . '>', $data[$placeholder], $code);
            }
        }

        return $code;
    }

    public static function getValueJson($field, $customFields = null)
    {
        if (!empty($customFields)) {
            $fields = json_decode($customFields);
            return $fields->$field;
        }
        return null;
    }

    public static function getCurrentRouteAction()
    {
        $controllerAction = Route::currentRouteAction();
        $listAction = explode('\\', $controllerAction);
        list($controller, $action) = explode('@', $listAction[4]);

        $result = [
            'full' => "{$controller}.{$action}",
            'controller' => "{$controller}",
            'action' => "{$action}"
        ];

        return $result;
    }

    public static function convertBase64ToFile($fileBase64, $isPublic = false, $folder = 'upload', $fileName = 'my_file', $fileExtension = ".png")
    {
        $bin = base64_decode($fileBase64);

        if($isPublic){
            $filePath = "public/{$folder}";
        } else {
            $filePath = "$folder";
        }

        if(!Storage::exists($filePath)){
            Storage::makeDirectory($filePath, 0775, true, true);
        }

        $fileFullPath = storage_path("app/{$filePath}/{$fileName}.{$fileExtension}");
        file_put_contents($fileFullPath, $bin);
        $fileSavePath = "{$folder}/{$fileName}.{$fileExtension}";

        return $fileSavePath;
    }

    public static function removeSpaceOnStr($str, $isUpper = true, $stripVietnamese = true, $hasHyphen = false, $noSymbols = true)
    {
        $newStr = '';
        $str = Normalizer::normalize($str, Normalizer::FORM_C);
        $str = trim($str);
        $str = preg_replace('/\s+/', ' ', $str); // Normalize multiple spaces to one

        if ($noSymbols) {
            /* vietnamese */
            $str = Str::ascii($str);
            $str = preg_replace('/[^A-Za-z0-9\s\-]/', '', $str);
        }

        if ($hasHyphen) {
            $str = str_replace(' ', '-', $str);
            $str = strtolower($str);
            // $str = Str::ascii($str);

            if ($stripVietnamese) {
                $str = self::stripVietnamese($str);
            }

            if ($isUpper) {
                $str = Str::upper($str);
            }

            return $str;
        }

        $parts = explode(' ', $str);

        foreach ($parts as $part) {
            if ($isUpper) {
                $part = Str::upper($part);
            }

            if ($stripVietnamese) {
                $part = Str::ascii($part);
            }

            $newStr .= $part;
        }

        return $newStr;
    }

    public static function stripVietnamese($str) {
        $str = Normalizer::normalize($str, Normalizer::FORM_C);
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        $str = Normalizer::normalize($str, Normalizer::FORM_C);
        return $str;
    }

    public static function checkEmailForm($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    public static function checkDateFormat($dateString) {
        $date = DateTime::createFromFormat('Y-m-d', $dateString);

        if ($date === false) {
            return false;
        }

        return $date->format('Y-m-d') === $dateString;
    }

    public static function compareDateToToday($dateString) {
        $date = DateTime::createFromFormat('Y-m-d', $dateString);
        $currentDate = new DateTime();

        if ($date > $currentDate) {
            return 1;
        } elseif ($date->format('Y-m-d') == $currentDate->format('Y-m-d')) {
            return 0;
        }

        return -1;
    }

    public static function calcTimeLeft($expiredDate)
    {
        $currentDateTime = Carbon::now();
        $expiredDateTime = Carbon::parse($expiredDate)->endOfDay();
        $diff = $currentDateTime->diff($expiredDateTime);
        $yearsLeft = $diff->y;
        $monthsLeft = $diff->m;
        $daysLeft = $diff->d;
        $hoursLeft = $diff->h;
        $minsLeft = $diff->i;

        $result = "Thời gian sử dụng còn lại:";

        if ($yearsLeft > 0) {
            $result .= " {$yearsLeft} năm, ";
        }

        if ($monthsLeft > 0) {
            $result .= " {$monthsLeft} tháng, ";
        }

        if ($daysLeft > 0) {
            $result .= " $daysLeft ngày và $hoursLeft giờ";
        } else {
            $result .= " $hoursLeft giờ $minsLeft phút";
        }

        return $result;
    }

    public static function checkTemplateEmail($templateId)
    {
        $templateId = resource_path("views/email-templates/{$templateId}.blade.php");

        if (File::exists($templateId)) {
            return true;
        }

        return false;
    }

    public static function convertImageToBase64($imagePath, $isPublic = false)
    {
        $image = storage_path($imagePath);

        if ($isPublic) {
            $image = public_path($imagePath);
        }

        try {
            if (File::exists($image)) {
                $base64 = base64_encode(file_get_contents($image));
                return $base64;
            }
        } catch (Exception $e) {
            Log::alert($e);
            return null;
        }

        return null;
    }

    public static function generateFileNameByTime($folderName, $randomeCodeLength = 3)
    {
        $year = date('Y');
        $month = date('m');
        $date = date('d');
        $hour = date('H');
        $min = date('i');
        $sec = date('s');
        $randomCode = strtolower(self::randomCode($randomeCodeLength));
        $syncDate = $year.$month.$date.'-'.$hour.$min.$sec;
        $fileName = "{$syncDate}-{$randomCode}.json";
        $filePath = "$folderName/{$year}/{$month}/{$fileName}";
        return $filePath;
    }

    public static function calcPercent($x, $y, $getDiff = false, $decimalPlaces = 2)
    {
        if ($y == 0) {
            return 100;
        }

        $percentage = ($x/$y)*100;

        if ($getDiff) {
            if ($x > $y) {
                $percentage = (($x - $y)/$y)*100;
            } else {
                $percentage = (($y - $x)/$y)*100;
            }
        }

        return round($percentage, $decimalPlaces);
    }

    public static function escapeSingleQuote($str)
    {
        return str_replace("'", "\'", $str);
    }

    public static function getAllMonthsFromYear($startYear = 2022)
    {
        $monthsByYear = [];

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $months = [];
            $endMonth = ($year == $currentYear) ? $currentMonth : 12;

            for ($month = 1; $month <= $endMonth; $month++) {
                $date = Carbon::createFromDate($year, $month, 1);
                // $months[] = $date->format('F');
                $months[] = $date->format('m');
            }

            $monthsByYear[$year] = $months;
        }

        return $monthsByYear;
    }

    public static function getUsernameFromEmail(string $email): string
    {
        return strstr($email, '@', true) ?: $email;
    }

    public static function convertHtmlToPlainText(string $html)
    {
        $plainText = strip_tags($html);                     // Remove all HTML tags
        $plainText = html_entity_decode($plainText);        // Decode HTML entities
        $plainText = preg_replace('/\s+/', ' ', $plainText); // Optional: normalize spaces
        $plainText = preg_replace('/([.?!])\s*/', "$1\r\n", $plainText); // Add line breaks
        $plainText = preg_replace('/<br\s*\/?>/i', "\n\r", $plainText); // Replace <br> with \n\r
        $plainText = preg_replace('/<\/p>/i', "\n\r", $plainText); // Replace </p> with \n\r
        $plainText = preg_replace('/<\/div>/i', "\n\r", $plainText);
        $plainText = trim($plainText);
        return $plainText;
    }

    public static function fillTemplatePlaceholders(string $html, array $params): string
    {
        foreach ($params as $key => $value) {
            $html = str_replace('{{' . $key . '}}', e($value), $html);
            $html = str_replace('{{ ' . $key . ' }}', e($value), $html);
        }

        return $html;
    }

    public static function getPlaceholdersForPostmark(array $htmls) /* {{ <value> }} */
    {
        // preg_match_all('/{{\s*(.*?)\s*}}/', $html, $matches);
        // $placeholders = array_unique($matches[1]);
        // return $placeholders;

        $allMatches = [];

        foreach ($htmls as $html) {
            preg_match_all('/{{\s*(.*?)\s*}}/', $html, $matches);
            if (!empty($matches[1])) {
                $allMatches = array_merge($allMatches, $matches[1]);
            }
        }

        return array_values(array_unique($allMatches));
    }
}
