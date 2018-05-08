<?php
/**
 * Copyright (c) 2018 Oliver Schöndorn
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OS\LocalCaptcha\Helper;


class SigningHelper
{
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
    public function __construct(string $signature, string $hashAlgorithm = 'sha256')
    {
        $this->signature = version_compare(PHP_VERSION, '7.1.2', '>=')
            ? hash_hkdf('sha256', $signature)
            : hash_hmac($hashAlgorithm, 'SIGNATURE', $signature, true); // @codeCoverageIgnore
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
        return $signature . $data;
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
        $signatureLength = mb_strlen(hash($this->hashAlgorithm, '', true), '8bit');
        $signature = mb_substr($signedData, 0, $signatureLength, '8bit');
        $payload = mb_substr($signedData, $signatureLength, null, '8bit');

        return [
            $payload,
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
