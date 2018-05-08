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


use OS\LocalCaptcha\Exception\DecryptionException;

class EncryptionHelper
{
    /**
     * @var string
     */
    private $encryptionKey;

    /**
     * @var string
     */
    private $encryptionMethod;

    /**
     * @var string
     */
    private $hashAlgorithm;

    /**
     * @var int
     */
    private $initializationVectorLength;

    /**
     * EncryptionHelper constructor.
     *
     * @param string $encryptionKey
     * @param string $encryptionMethod
     * @param string $hashAlgorithm
     */
    public function __construct(string $encryptionKey, string $encryptionMethod = 'aes-128-ctr', string $hashAlgorithm = 'md5')
    {
        $this->encryptionKey = openssl_digest($encryptionKey, 'sha256', true);
        $this->encryptionMethod = $encryptionMethod;
        $this->hashAlgorithm = $hashAlgorithm;
        $this->initializationVectorLength = openssl_cipher_iv_length($encryptionMethod);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public function hash(string $data): string
    {
        return hash($this->hashAlgorithm, $data, true);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public function encrypt(string $data): string
    {
        $initializationVector = $this->getInitializationVector();
        $encryptedData = openssl_encrypt($data, $this->encryptionMethod, $this->encryptionKey,
            OPENSSL_RAW_DATA, $initializationVector);

        return $initializationVector . $encryptedData;
    }

    /**
     * @param string $data
     *
     * @return string
     * @throws DecryptionException
     */
    public function decrypt(string $data): string
    {
        $initializationVector = mb_substr($data, 0, $this->initializationVectorLength, '8bit');
        $encryptedData = mb_substr($data, $this->initializationVectorLength, null, '8bit');

        $decryptedData = openssl_decrypt($encryptedData, $this->encryptionMethod, $this->encryptionKey,
            OPENSSL_RAW_DATA, $initializationVector);

        if ($decryptedData === false) {
            throw new DecryptionException(); // @codeCoverageIgnore
        }

        return $decryptedData;
    }

    private function getInitializationVector(): string
    {
        return openssl_random_pseudo_bytes($this->initializationVectorLength);
    }
}
