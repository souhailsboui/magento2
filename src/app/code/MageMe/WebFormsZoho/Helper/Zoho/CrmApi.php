<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebFormsZoho\Helper\Zoho;

use CURLFile;
use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class CrmApi
{
    private $apiDomain;
    private $accessToken;

    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param Curl $curl
     */
    public function __construct(
        LoggerInterface $logger,
        Curl            $curl
    )
    {
        $this->curl   = $curl;
        $this->logger = $logger;
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
    }

    /**
     * @param string|null $accessToken
     * @return $this
     */
    public function setAccessToken(?string $accessToken): CrmApi
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @param string|null $apiDomain
     * @return $this
     */
    public function setApiDomain(?string $apiDomain): CrmApi
    {
        $this->apiDomain = $apiDomain;
        return $this;
    }

    /**
     * @param array $lead
     * @return string
     * @throws Exception
     */
    public function insertLead(array $lead): string
    {
        $this->curl->setHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken
        ]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $data = [
            'data' => [$lead]
        ];
        $this->curl->post($this->apiDomain . '/crm/v2/Leads', json_encode($data));
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
        }
        if (!isset($response['data'])) {
            $message = $response['message'] ?? Api::UNEXPECTED_ERROR;
            $this->logger->error($message . ' body: ' . $this->curl->getBody());
            throw new Exception(__($message));
        }
        foreach ($response['data'] as $datum) {
            if ($datum['status'] == 'success') {
                return $datum['details']['id'];
            } else {
                $message = $datum['message'] ?? Api::UNEXPECTED_ERROR;
                $this->logger->error($message . ' body: ' . $this->curl->getBody());
                throw new Exception(__($message));
            }
        }
        $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
        throw new Exception(__(Api::UNEXPECTED_ERROR));
    }

    /**
     * @param string $id
     * @param array $file
     * @return string
     * @throws Exception
     */
    public function addLeadPhoto(string $id, array $file): string
    {
        $file = new CURLFile(realpath($file['path']), $file['type'], $file['name']);
        $data = [
            'file' => $file
        ];

        // Custom curl for files
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FTP | CURLPROTO_FTPS,
            CURLOPT_URL => $this->apiDomain . '/crm/v2/Leads/' . $id . '/photo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Authorization: Zoho-oauthtoken ' . $this->accessToken,
                'Content-Type: multipart/form-data'
            ]
        ]);
        $body = curl_exec($curl);
        $err  = curl_errno($curl);
        if ($err) {
            $message = curl_error($curl);
            $this->logger->error('Curl error: ' . $message);
            throw new Exception(__($message));
        }
        curl_close($curl);

        $response = json_decode($body, true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (!isset($response['status'])) {
            $message = $response['message'] ?? Api::UNEXPECTED_ERROR;
            $this->logger->error($message . ' body: ' . $this->curl->getBody());
            throw new Exception(__($message));
        }
        if ($response['status'] == 'success') {
            return $response['message'];
        } else {
            $message = $response['message'] ?? Api::UNEXPECTED_ERROR;
            $this->logger->error($message . ' body: ' . $this->curl->getBody());
            throw new Exception(__($message));
        }
    }

    /**
     * @param string $id
     * @param array $files
     * @return string
     * @throws Exception
     */
    public function addLeadFiles(string $id, array $files): string
    {
        $data = [];
        foreach ($files as $file) {
            $value = [];
            foreach ($file['value'] as $item) {
                $value[] = $this->uploadFile($item['path'], $item['type'], $item['name']);
            }
            $data[$file['field']] = $value;
        }
        $this->curl->setHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken
        ]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'PUT'
        ]);
        $data = [
            'data' => [$data]
        ];
        $this->curl->post($this->apiDomain . '/crm/v2/Leads/' . $id, json_encode($data));
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (!isset($response['data'])) {
            $message = $response['message'] ?? Api::UNEXPECTED_ERROR;
            $this->logger->error($message . ' body: ' . $this->curl->getBody());
            throw new Exception(__($message));
        }
        foreach ($response['data'] as $datum) {
            if ($datum['status'] == 'success') {
                return $datum['details']['id'];
            } else {
                $message = $datum['message'] ?? Api::UNEXPECTED_ERROR;
                $this->logger->error($message . ' body: ' . $this->curl->getBody());
                throw new Exception(__($message));
            }
        }
        $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
        throw new Exception(__(Api::UNEXPECTED_ERROR));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getLeadFields(): array
    {
        $this->curl->setHeaders(['Authorization' => 'Zoho-oauthtoken ' . $this->accessToken]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->curl->get($this->apiDomain . '/crm/v2/settings/fields?module=Leads');
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (isset($response['message'])) {
            $this->logger->error($response['message'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['message']));
        }
        return $response['fields'] ?? [];
    }

    /**
     * @param string $filePath
     * @param string $mimeType
     * @param string $fileName
     * @return string
     * @throws Exception
     */
    public function uploadFile(string $filePath, string $mimeType = '', string $fileName = ''): string
    {
        $file = new CURLFile(realpath($filePath), $mimeType, $fileName);
        $data = [
            'file' => $file
        ];

        // Custom curl for files
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FTP | CURLPROTO_FTPS,
            CURLOPT_URL => $this->apiDomain . '/crm/v2/files',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Authorization: Zoho-oauthtoken ' . $this->accessToken,
                'Content-Type: multipart/form-data'
            ]
        ]);
        $body = curl_exec($curl);
        $err  = curl_errno($curl);
        if ($err) {
            $message = curl_error($curl);
            $this->logger->error('Curl error: ' . $message);
            throw new Exception(__($message));
        }
        curl_close($curl);

        $response = json_decode($body, true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (!isset($response['data'])) {
            $message = $response['message'] ?? Api::UNEXPECTED_ERROR;
            $this->logger->error($message . ' body: ' . $this->curl->getBody());
            throw new Exception(__($message));
        }
        foreach ($response['data'] as $datum) {
            if ($datum['status'] == 'success') {
                return $datum['details']['id'];
            } else {
                $message = $datum['message'] ?? Api::UNEXPECTED_ERROR;
                $this->logger->error($message . ' body: ' . $this->curl->getBody());
                throw new Exception(__($message));
            }
        }
        $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
        throw new Exception(__(Api::UNEXPECTED_ERROR));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getUsers(): array
    {
        $this->curl->setHeaders(['Authorization' => 'Zoho-oauthtoken ' . $this->accessToken]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->curl->get($this->apiDomain . '/crm/v2/users');
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (isset($response['message'])) {
            $this->logger->error($response['message'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['message']));
        }
        return $response['users'] ?? [];
    }
}