<?php

namespace OS\LocalCaptcha\Helper;


class SigningHelper
{
    const DELIMITER = '.';

    /**
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $hashAlgorithm;

    /**
     * SigningHelper constructor.
     *
     * @param string $signature
     * @param string $hashAlgorithm
     */
    public function __construct(string $signature, string $hashAlgorithm = 'crc32')
    {
        $this->signature = hash_hkdf('sha256', $signature);
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * Returns the supplied data with the signature appended
     *
     * @param string $data
     *
     * @return string
     */
    public function sign(string $data): string
    {
        return $this->joinSignature($data, $this->getSignature($data));
    }

    private function joinSignature(string $data, string $signature): string
    {
        return $data . $this::DELIMITER . $signature;
    }

    private function getSignature(string $data): string
    {
        return hash_hmac($this->hashAlgorithm, $data, $this->signature, true);
    }

    /**
     * Verifies the supplied signed data
     *
     * @param string $signedData
     *
     * @return bool
     */
    public function verify(string $signedData): bool
    {
        list($data, $actualSignature) = $this->separateSignature($signedData);
        return hash_equals($this->getSignature($data), $actualSignature);
    }

    private function separateSignature(string $signedData): array
    {
        $parts = explode($this::DELIMITER, $signedData);
        $signature = array_pop($parts);

        return [
            implode($this::DELIMITER, $parts),
            $signature
        ];
    }

    /**
     * @param string $signedData
     *
     * @return string
     */
    public function getPayload(string $signedData): string
    {
        return $this->separateSignature($signedData)[0];
    }
}
