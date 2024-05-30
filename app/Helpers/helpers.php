<?php

use App\Models\State;

use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;


if (!function_exists('Get_Image_Base64')) {

    function Get_Only_Base64($base64_image)
    {
        return substr($base64_image, strpos($base64_image, ",") + 1);
    }
}

if (!function_exists('Get_Extension_Image_Base64')) {

    function Get_Extension_Image_Base64($base64_image, $full = null)
    {
        preg_match("/^data:image\/(.*);base64/i", $base64_image, $img_extension);

        return ($full) ?  $img_extension[0] : (Str::contains($img_extension[1], '+xml') ? Str::remove('+xml', $img_extension[1]) : $img_extension[1]);
    }
}



if (!function_exists('is_base64_image')) {

    function is_base64_image($base64)
    {
        try {

            if (empty($base64))
            {
                return false;
            }

            $base64 = trim($base64);

            if (strpos($base64, 'data:image/') !== 0)
            {
                return false;
            }

            $parts = explode(';', $base64);

            if (count($parts) !== 2 || !(stripos($parts[1], 'base64') !== false) )
            {
                return false;
            }

            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }
}
