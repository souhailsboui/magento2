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

class DeskApi
{
    private $deskApiDomain;
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
    public function setAccessToken(?string $accessToken): DeskApi
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @param string|null $deskApiDomain
     * @return $this
     */
    public function setDeskApiDomain(?string $deskApiDomain): DeskApi
    {
        $this->deskApiDomain = $deskApiDomain;
        return $this;
    }

    /**
     * @param bool $isEnabled
     * @return array
     * @throws Exception
     */
    public function getDepartments(bool $isEnabled = true): array
    {
        $this->curl->setHeaders(['Authorization' => 'Zoho-oauthtoken ' . $this->accessToken]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->curl->get($this->deskApiDomain . '/v1/departments' . ($isEnabled ? '?isEnabled=true' : ''));
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (isset($response['message'])) {
            $this->logger->error($response['message'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['message']));
        }
        return $response['data'] ?? [];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getContacts(): array
    {
        $this->curl->setHeaders(['Authorization' => 'Zoho-oauthtoken ' . $this->accessToken]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->curl->get($this->deskApiDomain . '/v1/contacts');
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (isset($response['message'])) {
            $this->logger->error($response['message'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['message']));
        }
        return $response['data'] ?? [];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAgents(): array
    {
        $this->curl->setHeaders(['Authorization' => 'Zoho-oauthtoken ' . $this->accessToken]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->curl->get($this->deskApiDomain . '/v1/agents');
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (isset($response['message'])) {
            $this->logger->error($response['message'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['message']));
        }
        return $response['data'] ?? [];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getChannels(): array
    {
        $this->curl->setHeaders(['Authorization' => 'Zoho-oauthtoken ' . $this->accessToken]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->curl->get($this->deskApiDomain . '/v1/channels');
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (isset($response['message'])) {
            $this->logger->error($response['message'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['message']));
        }
        return $response['data'] ?? [];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getLanguages(): array
    {
        $this->curl->setHeaders(['Authorization' => 'Zoho-oauthtoken ' . $this->accessToken]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->curl->get($this->deskApiDomain . '/v1/languages');
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
            throw new Exception(__(Api::UNEXPECTED_ERROR));
        }
        if (isset($response['message'])) {
            $this->logger->error($response['message'] . ' body: ' . $this->curl->getBody());
            throw new Exception(__($response['message']));
        }
        return $response['languages'] ?? [];
    }

    /**
     * @param array $ticket
     * @return string
     * @throws Exception
     */
    public function createTicket(array $ticket): string
    {
        $this->curl->setHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken
        ]);
        $this->curl->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->logger->error(json_encode($ticket));
        $this->curl->post($this->deskApiDomain . '/v1/tickets', json_encode($ticket));
        $response = json_decode($this->curl->getBody(), true);
        if (!is_array($response)) {
            $this->logger->error(Api::UNEXPECTED_ERROR . ' body: ' . $this->curl->getBody());
        }
        if (!isset($response['id'])) {
            $message = $response['message'] ?? Api::UNEXPECTED_ERROR;
            $this->logger->error($message . ' body: ' . $this->curl->getBody());
            throw new Exception(__($message));
        }
        return $response['id'];
    }

    /**
     * @param string $id
     * @param string $filePath
     * @param string $mimeType
     * @param string $fileName
     * @return string
     * @throws Exception
     */
    public function createTicketAttachment(
        string $id,
        string $filePath,
        string $mimeType = '',
        string $fileName = ''
    ): string {
        $file = new CURLFile(realpath($filePath), $mimeType, $fileName);
        $data = [
            'file' => $file
        ];

        // Custom curl for files
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FTP | CURLPROTO_FTPS,
            CURLOPT_URL => $this->deskApiDomain . "/v1/tickets/$id/attachments",
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
        if (!isset($response['id'])) {
            $message = $response['message'] ?? Api::UNEXPECTED_ERROR;
            $this->logger->error($message . ' body: ' . $this->curl->getBody());
            throw new Exception(__($message));
        }
        return $response['id'];
    }
}