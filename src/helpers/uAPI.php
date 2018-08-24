<?php

if (!function_exists('uAPI')) {

    function uAPI(array $config = []): EnnioSousa\uCozApi\uCozApi {
        return new EnnioSousa\uCozApi\uCozApi($config);
    }

}
