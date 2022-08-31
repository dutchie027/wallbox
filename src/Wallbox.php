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

use dutchie027\Wallbox\Exceptions\WallboxAPIRequestException;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Wallbox
{
    /**
     * Version of the Library
     *
     * @const string
     */
    protected const LIBRARY_VERSION = '0.5.0';

    /**
     * Status IDs
     *
     * @const array
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
     * NOTE: This is a v2 URI and uses PUT vs GET
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
     * Log Directory
     *
     * @var string
     */
    protected $p_log_location;

    /**
     * JWT Token
     *
     * @var string
     */
    protected $p_jwt;

    /**
     * Base 64 Token
     *
     * @var string
     */
    protected $p_token;

    /**
     * Log Reference
     *
     * @var string
     */
    protected $p_log;

    /**
     * Log Name
     *
     * @var string
     */
    protected $p_log_name;

    /**
     * Log File Tag
     *
     * @var string
     */
    protected $p_log_tag = 'wallbox';

    /**
     * Log Types
     *
     * @var array
     */
    protected $log_literals = [
        'debug',
        'info',
        'notice',
        'warning',
        'critical',
        'error',
    ];

    /**
     * The Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    public $guzzle;

    /**
     * Default constructor
     */
    public function __construct($user, $password, array $attributes = [], Guzzle $guzzle = null)
    {
        $tokenString = $user . ':' . $password;
        $base64 = base64_encode($tokenString);
        $this->p_token = $base64;

        if (isset($attributes['log_dir']) && is_dir($attributes['log_dir'])) {
            $this->p_log_location = $attributes['log_dir'];
        } else {
            $this->p_log_location = sys_get_temp_dir();
        }

        if (isset($attributes['log_name'])) {
            $this->p_log_name = $attributes['log_name'];

            if (!preg_match("/\.log$/", $this->p_log_name)) {
                $this->p_log_name .= '.log';
            }
        } else {
            $this->p_log_name = $this->pGenRandomString() . '.' . time() . '.log';
        }

        if (isset($attributes['log_tag'])) {
            $this->p_log = new Logger($attributes['log_tag']);
        } else {
            $this->p_log = new Logger($this->p_log_tag);
        }

        if (isset($attributes['log_level']) && in_array($attributes['log_level'], $this->log_literals, true)) {
            if ($attributes['log_level'] == 'debug') {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), \Monolog\Level::Debug));
            } elseif ($attributes['log_level'] == 'info') {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), \Monolog\Level::Info));
            } elseif ($attributes['log_level'] == 'notice') {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), \Monolog\Level::Notice));
            } elseif ($attributes['log_level'] == 'warning') {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), \Monolog\Level::Warning));
            } elseif ($attributes['log_level'] == 'error') {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), \Monolog\Level::Error));
            } elseif ($attributes['log_level'] == 'critical') {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), \Monolog\Level::Critical));
            } else {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), \Monolog\Level::Warning));
            }
        } else {
            $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), \Monolog\Level::Info));
        }
        $this->guzzle = $guzzle ?: new Guzzle();
        $this->usernamePasswordAuth();
    }

    /**
     * getLogLocation
     * Alias to Get Log Path
     *
     * @return string
     */
    public function getLogLocation()
    {
        return $this->pGetLogPath();
    }

    /**
     * getStatusName
     * Returns the name of the status associated with the ID
     *
     * @return string
     */
    public function getStatusName($id)
    {
        return self::STATUS_LOOKUP[$id];
    }

    /**
     * getJWTToken
     * Returns the stored JWT Token
     *
     * @return string
     */
    protected function getJWTToken()
    {
        return $this->p_jwt;
    }

    /**
     * getLogPointer
     * Returns a referencd to the logger
     *
     * @return object
     */
    public function getLogPointer()
    {
        return $this->p_log;
    }

    /**
     * pGetLogPath
     * Returns full path and name of the log file
     *
     * @return string
     */
    protected function pGetLogPath()
    {
        return $this->p_log_location . '/' . $this->p_log_name;
    }

    /**
     * getFullPayload
     *
     * @return string
     */
    public function getFullPayload()
    {
        $URL = self::API_URL . self::LIST_URI;

        return $this->makeAPICall('GET', $URL);
    }

    /**
     * setHeaders
     * Sets the headers using the API Token
     *
     * @return array
     */
    public function setHeaders($bearer = true)
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
            $array['Authorization'] = 'Basic ' . $this->p_token;
        }

        return $array;
    }

    private function usernamePasswordAuth()
    {
        $authURL = self::API_LOGIN . self::AUTH_URI;
        $this->p_jwt = json_decode($this->makeAPICall('GET', $authURL, false))->data->attributes->token;
    }

    /**
     * pGenRandomString
     * Generates a random string of $length
     *
     * @param int $length
     *
     * @return string
     */
    public function pGenRandomString($length = 6)
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
     * @param $type string GET|POST|DELETE|PATCH
     * @param $url string endpoint
     * @param $body string - usually passed as JSON
     *
     * @throws WallboxAPIRequestException Exception with details regarding the failed request
     *
     * @return Psr7\Stream Object
     */
    public function makeAPICall($type, $url, $token = true, $body = null)
    {
        $data['headers'] = $this->setHeaders($token);
        $data['body'] = $body;

        try {
            $request = $this->guzzle->request($type, $url, $data);

            return $request->getBody();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $ja = $response->getBody()->getContents();

                throw new WallboxAPIRequestException('An error occurred while performing the request to ' . $url . ' -> ' . ($ja['error'] ?? json_encode($ja)));
            }

            throw new WallboxAPIRequestException('An unknown error ocurred while performing the request to ' . $url);
        }
    }
}
