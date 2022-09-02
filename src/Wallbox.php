<?php

/**
 * PHP Wrapper to Interact with Wallbox API
 *
 * @version 2.0
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @see     https://github.com/dutche027/wallbox
 * @see     https://packagist.org/packages/dutchie027/wallbox
 */

namespace dutchie027\Wallbox;

use dutchie027\Wallbox\Exceptions\WallboxAPIException;
use dutchie027\Wallbox\Exceptions\WallboxAPIRequestException;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;

class Wallbox
{
    /**
     * Version of the Library
     *
     * @const string
     */
    protected const LIBRARY_VERSION = '0.1.0';

    /**
     * Status IDs
     *
     * @const array<mixed>
     */
    protected const STATUS_LOOKUP = [
        164 => 'WAITING',
        180 => 'WAITING',
        181 => 'WAITING',
        183 => 'WAITING',
        184 => 'WAITING',
        185 => 'WAITING',
        186 => 'WAITING',
        187 => 'WAITING',
        188 => 'WAITING',
        189 => 'WAITING',
        193 => 'CHARGING',
        194 => 'CHARGING',
        161 => 'READY',
        162 => 'READY',
        178 => 'PAUSED',
        182 => 'PAUSED',
        177 => 'SCHEDULED',
        179 => 'SCHEDULED',
        196 => 'DISCHARGING',
        14 => 'ERROR',
        15 => 'ERROR',
        0 => 'DISCONNECTED',
        163 => 'DISCONNECTED',
        209 => 'LOCKED',
        210 => 'LOCKED',
        165 => 'LOCKED',
        166 => 'UPDATING',
    ];

    /**
     * Root of the API
     *
     * @const string
     */
    protected const API_URL = 'https://api.wall-box.com';

    /**
     * Root of the LOGIN API
     *
     * @const string
     */
    protected const API_LOGIN = 'https://user-api.wall-box.com';

    /**
     * URI for Legacy Auth
     *
     * @const string
     */
    protected const LEGACY_AUTH_URI = '/auth/token/user';

    /**
     * URI for new Auth against new login URL
     *
     * @const string
     */
    protected const AUTH_URI = '/users/signin';

    /**
     * URI for Listing/Querying Data
     *
     * @const string
     */
    protected const LIST_URI = '/v3/chargers/groups';

    /**
     * URI specific for charger status
     *
     * @const string
     */
    protected const CHARGER_STATUS_URI = '/chargers/status/';

    /**
     * URI for Acting on a charger
     * NOTE: This is a v2 URI and uses PUT vs GET
     *
     * @const string
     */
    protected const CHARGER_ACTION_URI = '/v2/charger/';

    /**
     * URI for Acting on a charger
     *
     * @const string
     */
    protected const CHARGER_SESSION_ACTION_URI = '/v3/chargers/';

    /**
     * URI for Getting Session Data
     *
     * @const string
     */
    protected const SESSION_LIST_URI = '/v4/sessions/stats';

    /**
     * JWT Token
     *
     * @var string
     */
    protected $p_jwt;

    /**
     * The Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    public $guzzle;

    /**
     * The Config class
     *
     * @var Config
     */
    public $config;

    /**
     * The Push class
     *
     * @var Push
     */
    public $push;

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->config = $this->config ?: new Config();
        $this->guzzle = $this->guzzle ?: new Guzzle();
        $this->usernamePasswordAuth();
    }

    /**
     * getStatusName
     * Returns the name of the status associated with the ID
     */
    public function getStatusName($id): string
    {
        return self::STATUS_LOOKUP[$id];
    }

    /**
     * getStatusName
     * Returns the name of the status associated with the ID
     *
     * @param int $id
     */
    public function checkFirmwareStatus($id): string
    {
        $chargerConfig = json_decode($this->getChargerStatus($id));
        $cv = $chargerConfig->config_data->software->currentVersion;
        $lv = $chargerConfig->config_data->software->latestVersion;
        $ua = $chargerConfig->config_data->software->updateAvailable;

        if ($cv == $lv && !$ua) {
            return 'Firmware up to date';
        }

        return 'Firmware needs updated';
    }

    /**
     * getStatusName
     * Returns the name of the status associated with the ID
     *
     * @param int $id
     */
    public function checkLock($id): bool
    {
        $chargerConfig = json_decode($this->getChargerStatus($id));
        $locked = $chargerConfig->config_data->locked;

        return $locked == 1 ? true : false;
    }

    /**
     * getJWTToken
     * Returns the stored JWT Token
     */
    protected function getJWTToken(): string
    {
        return $this->p_jwt;
    }

    /**
     * getStats
     * Calls Stats URI and gets data between start and end
     *
     * @param int $id
     * @param int $start
     * @param int $end
     */
    public function getStats($id, $start, $end): string
    {
        $payload = [
            'charger' => $id,
            'start_date' => $start,
            'end_date' => $end,
        ];
        $httpPayload = http_build_query($payload);

        $URL = self::API_URL . self::SESSION_LIST_URI . '?' . $httpPayload;

        return $this->makeAPICall('GET', $URL);
    }

    /**
     * getChargerStatus
     * Returns full data about charger
     *
     * @param int $id
     */
    public function getChargerStatus($id): string
    {
        $URL = self::API_URL . self::CHARGER_STATUS_URI . $id;

        return $this->makeAPICall('GET', $URL);
    }

    /**
     * getFullPayload
     */
    public function getFullPayload(): string
    {
        $URL = self::API_URL . self::LIST_URI;

        return $this->makeAPICall('GET', $URL);
    }

    /**
     * setHeaders
     * Sets the headers using the API Token
     *
     * @param bool $bearer
     */
    public function setHeaders($bearer = true): array
    {
        $array = [
            'User-Agent' => 'php-api-dutchie027/' . self::LIBRARY_VERSION,
            'Content-Type' => 'application/json;charset=utf-8',
            'Accept' => 'application/json, text/plain, */*',
        ];

        if ($bearer) {
            $array['Authorization'] = 'Bearer ' . $this->getJWTToken();
        } else {
            $array['Partner'] = 'wallbox';
            $array['Authorization'] = 'Basic ' . $this->config->getToken();
        }

        return $array;
    }

    /**
     * usernamePasswordAuth
     */
    private function usernamePasswordAuth(): void
    {
        $authURL = self::API_LOGIN . self::AUTH_URI;
        $this->p_jwt = json_decode($this->makeAPICall('GET', $authURL, false))->data->attributes->token;
    }

    /**
     * getLastChargeDuration
     */
    public function getLastChargeDuration()
    {
        $data = json_decode($this->getFullPayload(), true);

        return $this->convertSeconds($data['result']['groups'][0]['chargers'][0]['chargingTime']);
    }

    /**
     * unlockCharger
     *
     * @param int $id
     */
    public function unlockCharger($id): void
    {
        $URL = self::API_URL . self::CHARGER_ACTION_URI . $id;
        $body = '{"locked":0}';
        $this->makeAPICall('PUT', $URL, true, $body);
    }

    /**
     * lockCharger
     *
     * @param int $id
     */
    public function lockCharger($id): void
    {
        $URL = self::API_URL . self::CHARGER_ACTION_URI . $id;
        $body = '{"locked":1}';
        $this->makeAPICall('PUT', $URL, true, $body);
    }

    /**
     * getChargerData

     *
     * @param int $id
     */
    public function getChargerData($id): string
    {
        $URL = self::API_URL . self::CHARGER_ACTION_URI . $id;

        return $this->makeAPICall('GET', $URL);
    }

    /**
     * getTotalChargeTime
     *
     * @param int $id
     */
    public function getTotalChargeTime($id): string
    {
        $data = json_decode($this->getChargerData($id));

        return $this->convertSeconds($data->data->chargerData->resume->chargingTime);
    }

    /**
     * getTotalSessions
     */
    public function getTotalSessions($id): string
    {
        $data = json_decode($this->getChargerData($id));

        return $data->data->chargerData->resume->totalSessions;
    }

    /**
     * pGenRandomString
     * Generates a random string of $length
     *
     * @param int $length
     */
    public function pGenRandomString($length = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * makeAPICall
     * Makes the API Call
     *
     * @param string $type  string GET|POST|DELETE|PATCH
     * @param string $url   string endpoint
     * @param string $body  string - usually passed as JSON
     * @param bool   $token
     *
     * @throws WallboxAPIRequestException Exception with details regarding the failed request
     */
    public function makeAPICall($type, $url, $token = true, $body = null): string
    {
        $data['headers'] = $this->setHeaders($token);
        $data['body'] = $body;

        try {
            $request = $this->guzzle->request($type, $url, $data);

            return $request->getBody()->getContents();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $ja = $response->getBody()->getContents();

                throw new WallboxAPIRequestException('An error occurred while performing the request to ' . $url . ' -> ' . ($ja['error'] ?? json_encode($ja)));
            }

            throw new WallboxAPIRequestException('An unknown error ocurred while performing the request to ' . $url);
        }
    }

    /**
     * convertSeconds
     * Returns a referencd to the logger
     */
    public function convertSeconds(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : ($minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s");
    }

    /**
     * convertSeconds
     * Returns a referencd to the logger
     */
    public function monitor(int $id, int $seconds = 30): void
    {
        if (php_sapi_name() !== "cli") {
            throw new WallboxAPIException('This call is only allwed to be made from the CLI');
        }

        $fp = fopen(__FILE__, 'r');

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            print "Tried to start up but already running. Exiting" . PHP_EOL;
            exit;
        }

        while (true) {
            Log::info("We're in monitor mode. Gonna start logging what I'm doing");
            sleep($seconds);
        }
    }

    /**
     * pushover
     * Pointer to the \Push class
     *
     */
    public function pushover(): Push
    {
        $po = new Push($this);
        return $po;
    }
}
