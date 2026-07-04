<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    protected $appends = [
        'logo_url',
        'login_background_image_url',
        'favicon_url'
    ];

    public function getLogoUrlAttribute()
    {
        if (is_null($this->logo)) {
            return asset('app-logo.png');
        }
        return asset_url('global-logo/' . $this->logo);
    }

    public function getFaviconUrlAttribute()
    {
        if (is_null($this->favicon)) {
            return asset('favicon/favicon-32x32.png');
        }
        return asset_url('favicon/' . $this->favicon);
    }

    public function currency() {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function getLoginBackgroundImageUrlAttribute()
    {
        return FrontCmsHeader::first()->login_background_image_url;
    }
}
