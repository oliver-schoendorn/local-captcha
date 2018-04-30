<?php

namespace OS\LocalCaptcha;


use OS\LocalCaptcha\Exception\DecryptionException;
use OS\LocalCaptcha\Exception\HoneypotNotEmptyException;
use OS\LocalCaptcha\Exception\HoneypotNotExistingException;
use OS\LocalCaptcha\Exception\InvalidFormIdException;
use OS\LocalCaptcha\Exception\InvalidMetaDataException;
use OS\LocalCaptcha\Exception\LocalCaptchaException;
use OS\LocalCaptcha\Exception\MissingMetaDataException;
use OS\LocalCaptcha\Exception\InvalidSignatureException;
use OS\LocalCaptcha\Exception\TimingException;
use OS\LocalCaptcha\Helper\EncryptionHelper;
use OS\LocalCaptcha\Helper\SigningHelper;

class FormData implements \ArrayAccess
{
    /**
     * @var string
     */
    private $formId;

    /**
     * @var array
     */
    private $data;

    /**
     * @var \ArrayObject|null
     */
    private $decryptedData;

    /**
     * @var FormMetaData
     */
    private $metaData;

    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;

    /**
     * @var SigningHelper
     */
    private $signingHelper;

    /**
     * FormData constructor.
     *
     * @param string           $formId
     * @param array            $submittedData
     * @param EncryptionHelper $encryptionHelper
     * @param SigningHelper    $signingHelper
     */
    public function __construct(string $formId, array $submittedData, EncryptionHelper $encryptionHelper, SigningHelper $signingHelper)
    {
        $this->formId = $formId;
        $this->data = $submittedData;
        $this->encryptionHelper = $encryptionHelper;
        $this->signingHelper = $signingHelper;
    }

    /**
     * @throws LocalCaptchaException
     */
    public function validate()
    {
        $this->metaData = $this->getMetaData();
        $this->validateMetaData();
        $this->validateHoneyPots();
    }

    /**
     * Extracts the meta data from the given form data.
     *
     * Throws exceptions if the meta data is not present, has an invalid signature, if
     * it can not be decrypted or if it can not be unserialized.
     *
     * @return FormMetaData
     *
     * @throws InvalidSignatureException
     * @throws MissingMetaDataException
     * @throws InvalidMetaDataException
     * @throws DecryptionException
     */
    private function getMetaData(): FormMetaData
    {
        // Get the mangled meta field key
        $expectedMetaFieldKey = base64_encode($this->encryptionHelper->hash('meta-data'));
        if ( ! array_key_exists($expectedMetaFieldKey, $this->data)) {
            throw new MissingMetaDataException();
        }

        // Get the meta data from the submitted form data
        $metaData = base64_decode($this->data[$expectedMetaFieldKey]);
        if ( ! $this->signingHelper->verify($metaData)) {
            throw new InvalidSignatureException();
        }

        // Remove meta field from form data to not confuse some poor devs
        unset($this->data[$expectedMetaFieldKey]);
        if ($this->decryptedData) {
            unset($this->decryptedData[$expectedMetaFieldKey]);
        }

        // Decrypt data
        $metaData = $this->signingHelper->getPayload($metaData);
        $metaData = $this->encryptionHelper->decrypt($metaData);

        // Unserialize data
        $metaData = json_decode($metaData);
        if ( ! $metaData) {
            throw new InvalidMetaDataException();
        }

        return FormMetaData::createFromArray((array) $metaData);
    }

    /**
     * @throws InvalidFormIdException
     * @throws TimingException
     */
    private function validateMetaData()
    {
        if ($this->metaData->getFormId() !== $this->formId) {
            throw new InvalidFormIdException();
        }

        // Check form generation DateTime was at least 30 seconds ago
        $validFrom = $this->metaData->getGenerationDate()->add(new \DateInterval('PT30S'));
        $validUntil = $this->metaData->getGenerationDate()->add(new \DateInterval('PT24H'));
        $now = new \DateTime();

        // User took less than 30 seconds to fill fields and hit the submit button
        if ($now < $validFrom) {
            throw new TimingException();
        }

        // User took more than 24h
        if ($now > $validUntil) {
            throw new TimingException();
        }
    }

    /**
     * @throws HoneypotNotEmptyException
     * @throws HoneypotNotExistingException
     */
    private function validateHoneyPots()
    {
        $formData = $this->getData();
        foreach ($this->metaData->getHoneypots() as $honeypot) {
            if ( ! array_key_exists($honeypot, $formData)) {
                throw new HoneypotNotExistingException($honeypot);
            }

            if ( ! empty($formData[$honeypot])) {
                throw new HoneypotNotEmptyException($honeypot, $formData[$honeypot]);
            }
        }
    }

    /**
     * @param string|int $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->getData());
    }

    /**
     * @param string|int $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->getData()[$offset];
        }

        return null;
    }

    /**
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->getData()[$offset] = $value;
    }

    /**
     * @param string|int $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->getData()[$offset]);
        }
    }

    /**
     * Returns the decrypted form data
     *
     * @return \ArrayObject
     */
    public function getData(): \ArrayObject
    {
        if ( ! $this->decryptedData) {
            $this->decryptedData = new \ArrayObject();
            foreach ($this->data as $key => $value) {
                $this->decryptedData[$this->tryDecryptKey($key)] = $value;
            }
        }

        return $this->decryptedData;
    }

    private function tryDecryptKey($key)
    {
        try {
            $decodedKey = base64_decode($key);

            if ($decodedKey && substr($decodedKey, 0, 3) === '_e:') {
                $key = $this->encryptionHelper->decrypt(substr($decodedKey, 3));
            }

            return $key;
        }
        catch (\Exception $e) { return $key; } // @codeCoverageIgnore
    }
}
