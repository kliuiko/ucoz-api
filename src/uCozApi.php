<?php

/*
 * A set of methods for querying the uCoz API. Version for uCoz PHP server
 * @author Sergey Driver86 Pugovkin <sergey@pugovk.in> - developer of methods for querying (php version)
 * @author Dmitry Kiselev <api@ucoz.net> - modification and adaptation for uAPI + images. api.ucoz.net
 * @version 2.3 from October 1, 2016
 */

namespace EnnioSousa\uCozApi;

use Illuminate\Support\Facades\Cache;

class uCozApi {

    public static $config;
    private static $params;
    private static $website = '';
    private static $cache_ttl = 60 * 5;
    private static $last_result = null;

    /**
     * Class constructor
     * @param array $config Settings
     */
    public function __construct(array $config = []) {
        if (!empty($config)) {
            // setting uAPI config
            self::$config = array_only($config, array_keys(config('ucoz-api.oauth')));
            foreach (array_except($config, array_keys(config('ucoz-api.oauth'))) as $key => $value) {
                if (isset(self::$$key))
                    self::$$key = $value;
            }
        } else {
            if(config('ucoz-api.oauth'))
                self::$config = config('ucoz-api.oauth');
            else
                throw new \Exception('You need to publish config vendor using the command [php artisan vendor:publish --provider="EnnioSousa\uCozApi\uCozApiServiceProvider" --tag="config"]');

            foreach (array_except(config('ucoz-api'), array_keys(config('ucoz-api.oauth'))) as $key => $value) {
                if (isset(self::$$key))
                    self::$$key = $value;
            }
        }
        self::$params = [
            'oauth_version' => '1.0',
            'oauth_timestamp' => time(),
            'oauth_nonce' => md5(microtime() . mt_rand()),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_consumer_key' => self::$config['oauth_consumer_key'],
            'oauth_token' => self::$config['oauth_token'],
        ];
    }

    public static function toString() {
        return self::$last_result;
    }

    public static function toJson() {
        return self::toString();
    }
    
    public function __toString() {
        return self::toString();
    }

    public static function toArray(){
        return json_decode(self::$last_result, $assoc = true);
    }

    public static function toObject(){
        return json_decode(self::$last_result, $assoc = false);
    }

    /**
     * Creating a request signature
     * @param string $method    Request method, such as GET
     * @param string $url       Request URL, for example /blog
     * @param string $params    All the parameters passed through the URL when calling API.
     * @return string
     */
    private static function getSignature($method, $url, $params) {
        ksort($params);
        $baseString = strtoupper($method) . '&' . urlencode($url) . '&' . urlencode(strtr(http_build_query($params), ['+' => '%20']));
        return urlencode(base64_encode(hash_hmac('sha1', $baseString, self::$config['oauth_consumer_secret'] . '&' . self::$config['oauth_token_secret'], true)));
    }

    /**
     * Returns the base file name to use in the request signature
     * @param array $match  Matches when searching by regular expression preg_replace_callback
     * @return string
     */
    private static function getBaseName($match) {
        return basename($match[1]);
    }

    /**
     * A request to an API using the GET method
     * @param string $url   Request URL, for example /blog
     * @param array $data   Data array
     * @return array
     */
    public static function get($url, $data = []) {
        self::$params['oauth_nonce'] = md5(microtime() . mt_rand());
        $url = self::$website . 'uapi' . trim(strtolower($url), '') . '';
        $queryString = http_build_query(self::$params + $data + ['oauth_signature' => self::getSignature('GET', $url, self::$params + $data)]);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_URL, $url . '?' . $queryString);
        self::$last_result = curl_exec($curl);
        curl_close($curl);
        return new static; 
    }

    /**
     * A request to an API using the GET method
     * @param string $url   Request URL, for example /blog
     * @param array $data   Data array
     * @return array
     */
    public static function getCached($url, $data = []) {
        $key = 'ucoz_api__' . self::$website . $url . '?' . http_build_query($data);
        self::$last_result = Cache::remember($key, now()->addSeconds(self::$cache_ttl), function() use ($url, $data) {
            self::get($url, $data);
            return self::$last_result;
        });
        return new static; 
    }

    /**
     * A request to an API with POST method
     * @param string $url   Request URL, for example /blog
     * @param array $data   Data array
     * @return array
     */
    public static function post($url, $data) {
        self::$params['oauth_nonce'] = md5(microtime() . mt_rand());

        // Make image when sending sent and not collapse into a invalid signature
        $x = 1;
        while ($x < 50) {
            if (empty($data['file' . $x]))
                break;
            $getfile1others = basename($data['file' . $x]);
            $findme = '@';
            $pos = strpos($getfile1others, $findme);
            if ($pos === false) {
                $getfile1shop_array = ['file' . $x => '@' . $getfile1others];
            } else {
                $getfile1shop_array = ['file' . $x => '' . $getfile1others];
            }
            unset($data['file' . $x]);
            $data = array_merge($getfile1shop_array, $data);
            $x++;
        }

        if (!empty($data['file_add_cnt'])) {
            $allcountfilesshop = $data['file_add_cnt'];
        }

        if ($url == '/shop/editgoods') {

            $i = $allcountfilesshop;
            while ($i < 50) {
                if (empty($data['file_add_' . $i]) && $data['file_add_' . $i] != 'file_add_cnt')
                    break;
                $getfile1shop = basename($data['file_add_' . $i]);
                $findme = '@';
                $pos = strpos($getfile1shop, $findme);
                if ($pos === false) {
                    $getfile1shop_array = ['file_add_' . $i => '@' . $getfile1shop];
                } else {
                    $getfile1shop_array = ['file_add_' . $i => '' . $getfile1shop];
                }
                unset($data['file_add_' . $i]);
                $data = array_merge($getfile1shop_array, $data);
                $i++;
            }
        }

        $url = self::$website . 'uapi' . trim(strtolower($url), '') . '/';
        $sign = ['oauth_signature' => self::getSignature('POST', $url, self::$params + preg_replace_callback('/^@(.+)$/', [uCozApi::class, 'getBaseName'], $data))];
        $queryString = http_build_query($sign);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_URL, $url . '?' . $forcurlpost);
        curl_setopt($curl, CURLOPT_POST, true);
        $forcurlpost = array_merge(self::$params + $data, $sign);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $forcurlpost);
        self::$last_result = curl_exec($curl);
        curl_close($curl);
        return new static; 
    }

    /**
     * A request to an API with PUT method
     * @param string $url   Request URL, for example /blog
     * @param array $data   Data array
     * @return array
     */
    public static function put($url, $data) {
        self::$params['oauth_nonce'] = md5(microtime() . mt_rand());

        // Make image when sending sent and not collapse into a invalid signature
        $x = 1;
        while ($x < 50) {
            if (empty($data['file' . $x]))
                break;
            $getfile1others = basename($data['file' . $x]);
            $findme = '@';
            $pos = strpos($getfile1others, $findme);
            if ($pos === false) {
                $getfile1shop_array = ['file' . $x => '@' . $getfile1others];
            } else {
                $getfile1shop_array = ['file' . $x => '' . $getfile1others];
            }
            unset($data['file' . $x]);
            $data = array_merge($getfile1shop_array, $data);
            $x++;
        }

        if (!empty($data['file_add_cnt'])) {
            $allcountfilesshop = $data['file_add_cnt'];
        }

        if ($url == '/shop/editgoods') {

            $i = $allcountfilesshop;
            while ($i < 50) {
                if (empty($data['file_add_' . $i]) && $data['file_add_' . $i] != 'file_add_cnt')
                    break;
                $getfile1shop = basename($data['file_add_' . $i]);
                $findme = '@';
                $pos = strpos($getfile1shop, $findme);
                if ($pos === false) {
                    $getfile1shop_array = ['file_add_' . $i => '@' . $getfile1shop];
                } else {
                    $getfile1shop_array = ['file_add_' . $i => '' . $getfile1shop];
                }
                unset($data['file_add_' . $i]);
                $data = array_merge($getfile1shop_array, $data);
                $i++;
            }
        }

        $url = self::$website . 'uapi' . trim(strtolower($url), '') . '/';
        $sign = ['oauth_signature' => self::getSignature('PUT', $url, self::$params + preg_replace_callback('/^@(.+)$/', [uCozApi::class, 'getBaseName'], $data))];
        $queryString = http_build_query($sign);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        $forcurlpost = array_merge(self::$params + $data, $sign);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $forcurlpost);
        self::$last_result = curl_exec($curl);
        curl_close($curl);
        return new static; 
    }

    /**
     * A request to an API method DELETE
     * @param string $url   Request URL, for example /blog
     * @param array $data   Data array
     * @return array
     */
    public static function delete($url, $data) {
        self::$params['oauth_nonce'] = md5(microtime() . mt_rand());
        $url = self::$website . 'uapi' . trim(strtolower($url), '') . '/';
        $queryString = http_build_query(self::$params + $data + ['oauth_signature' => self::getSignature('DELETE', $url, self::$params + $data)]);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_URL, $url . '?' . $queryString);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        self::$last_result = curl_exec($curl);
        curl_close($curl);
        return new static; 
    }

}
