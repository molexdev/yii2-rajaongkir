<?php

/**
 * RajaOngkir PHP Client for Yii2
 * 
 * Class PHP for consume RajaOngkir API
 * Reference : http://rajaongkir.com/dokumentasi
 * 
 * @author Agus Suhartono <agus.suhartono@gmail.com>
 */

namespace molex\rajaongkir;

use yii\base\Component;
use yii\httpclient\Client as HttpClient;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Client extends Component
{
    const TYPE_STARTER = 'starter';
    const TYPE_BASIC = 'basic';
    const TYPE_PRO = 'pro';

    public $accountType = self::TYPE_STARTER;

    public static $validAccountType = [
        self::TYPE_STARTER,
        self::TYPE_BASIC,
        self::TYPE_PRO
    ];

    public $apiKey;
    public $baseUrl = "http://api.rajaongkir.com/";

    /**
     * Http CLient object
     *
     * @var \yii\httpclient\Client
     */
    public $httpClient;

    /**
     * Http Request Object
     *
     * @var \yii\httpclient\Request
     */
    public $request;

    /**
     * Constructor
     * 
     * @param string $apiKey API Key of RajaOngkir
     * @param string $accountType Account tyoe of RajaOngkir
     * @param array $additionalHeaders additional headers like 'android-key', 'ios-key', etc.
     */
    public function __construct($apiKey, $accountType = "starter", $additionalHeaders = array())
    {
        if (!$this->isValidAccountType($accountType)) {
            throw new \InvalidArgumentException("Unknown account type. Please provide the correct one.");
        }

        $this->accountType = $accountType;

        if ($accountType == self::TYPE_PRO) {
            $this->baseUrl = "http://pro.rajaongkir.com/api/";
        } else {
            $this->baseUrl .= "{$accountType}/";
        }

        $this->apiKey = $apiKey;
        $this->httpClient = new HttpClient(['baseUrl' => $this->baseUrl]);
        $this->request = $this->httpClient->createRequest();

        $defaultHeaders = [
            'content-type' => 'application/x-www-form-urlencoded',
            'key' => $this->apiKey
        ];
        $headers = ArrayHelper::merge($defaultHeaders, $additionalHeaders);

        $this->request->addHeaders($headers);
    }

    /**
     * Get Province
     * 
     * @param integer $province_id ID of province, if NULL generate all province
     * @return array Array of result
     */
    public function getProvince($province_id = NULL)
    {
        $params = ['province'];
        if (!is_null($province_id)) {
            $params['province'] =  $province_id;
        }

        $response = $this->request
            ->setUrl($params)
            ->send();

        return Json::decode($response->getContent());
    }

    /**
     * Get City
     * 
     * @param integer $province_id ID of province
     * @param integer $city_id ID of city, if $province_id and $city_id is NULL,  then generate all cities
     * @return array Array of result
     */
    public function getCity($province_id = NULL, $city_id = NULL)
    {
        $params = ['city'];

        if (!is_null($province_id)) {
            $params['province'] = $province_id;
        }
        if (!is_null($city_id)) {
            $params['id'] = $city_id;
        }

        $response = $this->request
            ->setUrl($params)
            ->send();

        return Json::decode($response->getContent());
    }

    /**
     * Get Subdistric (only for RajaOngkir Pro)
     * 
     * @param integer $city_id ID of city
     * @param integer $subdistrict_id ID of Subdistric
     * @return array Array of result
     */
    public function getSubdistrict($city_id, $subdistrict_id = NULL)
    {
        $params = [
            'subdistrict',
            'city' => $city_id
        ];
        if (!is_null($subdistrict_id)) {
            $params['id'] = $subdistrict_id;
        }

        $response = $this->request
            ->setUrl($params)
            ->send();

        return Json::decode($response->getContent());
    }

    /**
     * Calculate Cost
     * 
     * @param integer $origin ID of city or subdistric
     * @param integer $destination ID of city or subdistric
     * @param integer $weight 
     * @param string $courier code of courier, multicourier use colon (:) character
     * @param string $originType type of origin (city / subdistrict)
     * @param string $destinationType type of destination (city / subdistrict)
     * @return array array of result
     */
    public function getCost($origin, $destination, $weight = 1000, $courier = NULL, $originType = NULL, $destinationType = NULL)
    {
        $params = [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight
        ];
        if (!is_null($courier)) {
            $params['courier'] = $courier;
        }
        if (in_array($originType, array("city", "subdistrict"))) {
            $params['originType'] = $originType;
        }
        if (in_array($destinationType, array("city", "subdistrict"))) {
            $params['destinationType'] = $destinationType;
        }

        $response = $this->request
            ->setMethod('POST')
            ->setUrl('cost')
            ->setData($params)
            ->send();

        return Json::decode($response->getContent());
    }

    /**
     * Get International Origin
     * 
     * @param integer $city_id ID of city
     * @param integer $province_id ID of province
     * @return array array of result
     */
    public function getInternationalOrigin($city_id  = NULL, $province_id = NULL)
    {
        $params = ['v2/internationalOrigin'];
        if (!is_null($city_id)) {
            $params['id'] = $city_id;
        }
        if (!is_null($province_id)) {
            $params['province'] = $province_id;
        }

        $response = $this->request
            ->setUrl($params)
            ->send();

        return Json::decode($response->getContent());
    }


    /**
     * Get International Destination
     * 
     * @param integer $country_id ID of country, if null, generate all countries
     * @return array Array of result
     */
    public function getInternationalDestination($country_id = NULL)
    {
        $params = ['v2/internationalDestination'];
        if (!is_null($country_id)) {
            $params['id'] = $country_id;
        }

        $response = $this->request
            ->setUrl($params)
            ->send();

        return Json::decode($response->getContent());
    }


    /**
     * Get International Cost
     * 
     * @param integer $origin ID of origin city
     * @param integer $destination ID of destination country
     * @param integer $weight weight in gram
     * @param string $courier code of courier
     * @return array Array of result
     */
    public function getInternationalCost($origin, $destination, $weight = 1000, $courier = null)
    {

        $params = [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
        ];
        if (!is_null($courier)) {
            $params['courier'] = $courier;
        }

        $response = $this->request
            ->setMethod('POST')
            ->setUrl('v2/internationalCost')
            ->setData($params)
            ->send();

        return Json::decode($response->getContent());
    }

    /**
     * Get Waybill
     * 
     * @param integer $waybill number of waybill
     * @param string $courier code of courier
     * @return array Array of result
     */
    public function getWaybill($waybill, $courier)
    {
        $params = [
            'waybill' => $waybill,
            'courier' => $courier
        ];

        $response = $this->request
            ->setMethod('POST')
            ->setUrl('waybill')
            ->setData($params)
            ->send();

        return Json::decode($response->getContent());
    }


    /**
     * Check is Accoun type valid?
     *
     * @param string $accountType
     * @return boolean true is valid, other false
     */
    private function isValidAccountType($accountType)
    {
        if (in_array($accountType, self::$validAccountType)) {
            return true;
        }
        return false;
    }
}
