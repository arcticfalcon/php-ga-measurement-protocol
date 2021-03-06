<?php

namespace TheIconic\Tracking\GoogleAnalytics\Network;

use TheIconic\Tracking\GoogleAnalytics\AnalyticsResponse;
use TheIconic\Tracking\GoogleAnalytics\Parameters\SingleParameter;
use TheIconic\Tracking\GoogleAnalytics\Parameters\CompoundParameterCollection;
use GuzzleHttp\Client;

/**
 * Class HttpClient
 *
 * @package TheIconic\Tracking\GoogleAnalytics
 */
class HttpClient
{
    /**
     * HTTP client.
     *
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $payloadParameters;

    /**
     * Sets HTTP client.
     *
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getPayloadParameters()
    {
        return $this->payloadParameters;
    }

    /**
     * Gets HTTP client for internal class use.
     *
     * @return Client
     */
    private function getClient()
    {
        if ($this->client === null) {
            // @codeCoverageIgnoreStart
            $this->setClient(new Client());
        }
        // @codeCoverageIgnoreEnd

        return $this->client;
    }

    /**
     * Sends request to Google Analytics. Returns 200 if communication was succesful.
     *
     * @param string $url
     * @param SingleParameter[] $singleParameters
     * @param CompoundParameterCollection[] $compoundParameters
     * @return AnalyticsResponse
     */
    public function post($url, array $singleParameters, array $compoundParameters)
    {
        $singlesPost = $this->getSingleParametersPayload($singleParameters);

        $compoundsPost = $this->getCompoundParametersPayload($compoundParameters);

        $this->payloadParameters = array_merge($singlesPost, $compoundsPost);

        $request = $this->getClient()->createRequest('POST', $url, [
            'query' => $this->payloadParameters
        ]);

        $response = $this->getClient()->send($request);

        return new AnalyticsResponse($request, $response);
    }

    /**
     * Prepares all the Single Parameters to be sent to GA.
     *
     * @param SingleParameter[] $singleParameters
     * @return array
     */
    private function getSingleParametersPayload(array $singleParameters)
    {
        $postData = [];

        foreach ($singleParameters as $parameterObj) {
            $postData[$parameterObj->getName()] = $parameterObj->getValue();
        }

        return $postData;
    }

    /**
     * Prepares compound parameters inside collections to be sent to GA.
     *
     * @param CompoundParameterCollection[] $compoundParameters
     * @return array
     */
    private function getCompoundParametersPayload(array $compoundParameters)
    {
        $postData = [];

        foreach ($compoundParameters as $compoundCollection) {
            $parameterArray = $compoundCollection->getParametersArray();
            $postData = array_merge($postData, $parameterArray);
        }

        return $postData;
    }
}
