<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

class AssetController extends Controller
{
    /**
     * Return the users.
     */
    public function getMedias()
    {
        $medias = [
            'logo'                  => "assets/images/logo.png",
            'logo_white'            => "assets/images/logo-white.png",
            'favicon'               => "assets/images/brand/favicon.png",
            'favicon_ico'           => "favicon.ico",
            'bg_main'               => "assets/images/backgrounds/building.jpg",
            'img_login'             => "assets/images/backgrounds/checkin-login.png",
            'img_checkin'           => "assets/images/backgrounds/checkin.png",
            'placeholder_qrcode'    => "assets/images/placeholders/qrcode.png",
        ];

        foreach ($medias as $media => $path) {
            if (!file_exists(public_path($path))) {
                $medias[$media] = null;
                continue;
            }

            $medias[$media] = asset($path);
        }

        // Trả về đường dẫn URL
        return $this->responseSuccess($medias, null);
    }
}
