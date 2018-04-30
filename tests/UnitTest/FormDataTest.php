<?php

namespace OS\LocalCaptcha\UnitTest;

use OS\LocalCaptcha\Exception\HoneypotNotEmptyException;
use OS\LocalCaptcha\Exception\HoneypotNotExistingException;
use OS\LocalCaptcha\Exception\InvalidFormIdException;
use OS\LocalCaptcha\Exception\InvalidMetaDataException;
use OS\LocalCaptcha\Exception\InvalidSignatureException;
use OS\LocalCaptcha\Exception\MissingMetaDataException;
use OS\LocalCaptcha\Exception\TimingException;
use OS\LocalCaptcha\FormData;
use OS\LocalCaptcha\FormMetaData;
use OS\LocalCaptcha\Helper\EncryptionHelper;
use OS\LocalCaptcha\Helper\Inspector;
use OS\LocalCaptcha\Helper\SigningHelper;
use PHPUnit\Framework\TestCase;

class FormDataTest extends TestCase
{
    /**
     * @var string
     */
    private static $formId;

    /**
     * @var string
     */
    private static $encryptionKey;

    /**
     * @var EncryptionHelper
     */
    private static $encryptionHelper;

    /**
     * @var SigningHelper
     */
    private static $signingHelper;

    /**
     * Prepares all necessary helpers and data for the form data tests
     *
     * @throws \Exception
     */
    public static function setUpBeforeClass()
    {
        static::$formId = 'unit-testing-form';
        static::$encryptionKey = 'unit-testing-is-fun';

        static::$encryptionHelper = new EncryptionHelper(static::$encryptionKey);
        static::$signingHelper = new SigningHelper(static::$encryptionKey);

        parent::setUpBeforeClass();
    }

    private function makeSubject(array $submittedData, FormMetaData $metaData = null): FormData
    {
        $parsedData = [];
        foreach ($submittedData as $key => $value) {
            $parsedData[base64_encode('_e:' . $this::$encryptionHelper->encrypt($key))] = $value;
        }

        if ($metaData) {
            $encryptedMetaData = $this::$encryptionHelper->encrypt(json_encode($metaData));
            $parsedData = $this->addMetaDataStringToSubmittedData($parsedData,
                base64_encode($this::$signingHelper->sign($encryptedMetaData)));
        }

        return new FormData($this::$formId, $parsedData, $this::$encryptionHelper, $this::$signingHelper);
    }

    private function addMetaDataStringToSubmittedData(array $submittedData, string $encodedMetaData): array
    {
        $submittedData[base64_encode($this::$encryptionHelper->hash('meta-data'))] = $encodedMetaData;
        return $submittedData;
    }

    private function makeFormMetaData(\DateTimeImmutable $generationDate, array $honeypots = []): FormMetaData
    {
        return new FormMetaData($this::$formId, $generationDate, $honeypots);
    }

    /**
     * @throws \ReflectionException
     */
    public function testConstruct()
    {
        $subject = $this->makeSubject(['foo' => 'bar']);

        $inspector = new Inspector($subject);
        verify($inspector->get('formId'))->equals($this::$formId);
        verify($inspector->get('encryptionHelper'))->equals($this::$encryptionHelper);
        verify($inspector->get('signingHelper'))->equals($this::$signingHelper);
    }

    public function testOffsetExists()
    {
        $subject = $this->makeSubject(['foo' => 'bar', 'baz' => 'lol']);

        verify($subject->offsetExists('foo'))->true();
        verify($subject->offsetExists('baz'))->true();
        verify($subject->offsetExists('baz'))->true();
        verify($subject->offsetExists('bar'))->false();
    }

    public function testOffsetGet()
    {
        $subject = $this->makeSubject(['foo' => 'bar', 'baz' => 'lol']);

        verify($subject->offsetGet('foo'))->equals('bar');
        verify($subject->offsetGet('baz'))->equals('lol');
        verify($subject->offsetGet('bar'))->equals(null);
    }

    public function testOffsetUnset()
    {
        $subject = $this->makeSubject(['foo' => 'bar', 'baz' => 'lol']);
        verify($subject->offsetGet('foo'))->equals('bar');

        $subject->offsetUnset('foo');
        verify($subject->offsetGet('foo'))->equals(null);
    }

    public function testOffsetSet()
    {
        $subject = $this->makeSubject(['foo' => 'bar', 'baz' => 'lol']);
        verify($subject->offsetGet('foo'))->equals('bar');

        $subject->offsetSet('foo', 'lololo');
        verify($subject->offsetGet('foo'))->equals('lololo');
    }

    public function validationDataProvider()
    {
        return [
            'valid' => [
                [ 'foo' => 'bar', 'hp1' => '' ],
                new \DateTimeImmutable('-60 seconds'),
                [ 'hp1' ]
            ],
            'missing honeypot' => [
                [ 'foo' => 'bar', 'hp1' => '' ],
                new \DateTimeImmutable('-60 seconds'),
                [ 'hp1', 'hp2' ],
                HoneypotNotExistingException::class
            ],
            'honeypot populated' => [
                [ 'foo' => 'bar', 'hp1' => '', 'hp2' => 'basdada' ],
                new \DateTimeImmutable('-60 seconds'),
                [ 'hp1', 'hp2' ],
                HoneypotNotEmptyException::class
            ],
            'meta data missing' => [
                [ 'foo' => 'bar', 'hp1' => '' ],
                null,
                null,
                MissingMetaDataException::class
            ],
            'invalid timing - too early' => [
                [ 'foo' => 'bar', 'hp1' => '' ],
                new \DateTimeImmutable('-2 seconds'),
                [ 'hp1' ],
                TimingException::class
            ],
            'invalid timing - too old' => [
                [ 'foo' => 'bar', 'hp1' => '' ],
                new \DateTimeImmutable('-60 hours'),
                [ 'hp1' ],
                TimingException::class
            ]
        ];
    }

    /**
     * @param array $submittedData
     * @param \DateTimeImmutable|null $generationDate
     * @param array|null $honeypots
     * @param string|null $expectedException
     *
     * @throws \Throwable
     *
     * @dataProvider validationDataProvider
     */
    public function testValidate(
        array $submittedData,
        \DateTimeImmutable $generationDate = null,
        array $honeypots = null,
        string $expectedException = null
    ) {
        $meta = $generationDate && $honeypots
            ? $this->makeFormMetaData($generationDate, $honeypots)
            : null;
        $subject = $this->makeSubject($submittedData, $meta);

        try {
            $subject->validate();
        }
        catch (\Throwable $throwable) {
            if ($expectedException) {
                verify($throwable)->isInstanceOf($expectedException);
            }
            else {
                throw $throwable;
            }
        }

        if ( ! $expectedException) {
            verify($subject->getData()->getArrayCopy())->equals($submittedData);
        }
    }

    public function testValidateWithInvalidMetaDataSignature()
    {
        $submittedData = [ 'foo' => 'bar', 'hp' => '' ];
        $metaData = $this->makeFormMetaData(new \DateTimeImmutable(), [ 'hp' ]);

        $subject = $this->makeSubject($submittedData);
        $inspector = new Inspector($subject);

        $signingHelper = new SigningHelper('invalid-key');
        $parsedData = $inspector->get('data');
        $encryptedMetaData = $this::$encryptionHelper->encrypt(json_encode($metaData));
        $parsedData = $this->addMetaDataStringToSubmittedData($parsedData,
            base64_encode($signingHelper->sign($encryptedMetaData)));
        $inspector->set('data', $parsedData);

        $this->expectException(InvalidSignatureException::class);
        $subject->validate();
    }

    public function testValidateWillUnsetMetaDataFromFormData()
    {
        $submittedData = [ 'foo' => 'bar', 'hp' => '' ];
        $metaData = $this->makeFormMetaData(new \DateTimeImmutable('-120 seconds'), [ 'hp' ]);
        $subject = $this->makeSubject($submittedData, $metaData);

        verify($subject->getData()->getArrayCopy())->notEquals($submittedData);
        $subject->validate();
        verify($subject->getData()->getArrayCopy())->equals($submittedData);
    }

    public function testValidateWithBrokenMetaData()
    {
        $submittedData = [ 'foo' => 'bar', 'hp' => '' ];
        $subject = $this->makeSubject($submittedData);
        $inspector = new Inspector($subject);

        $parsedData = $inspector->get('data');
        $metaData = $this::$encryptionHelper->encrypt('{"broken": true');
        $parsedData = $this->addMetaDataStringToSubmittedData($parsedData, base64_encode(
            $this::$signingHelper->sign($metaData)
        ));
        $inspector->set('data', $parsedData);

        $this->expectException(InvalidMetaDataException::class);
        $subject->validate();
    }

    public function testValidateWithInvalidFormId()
    {
        $submittedData = [ 'foo' => 'bar', 'hp' => '' ];
        $metaData = $this->makeFormMetaData(new \DateTimeImmutable(), [ 'hp' ]);
        (new Inspector($metaData))->set('formId', 'invalid-form-id');

        $subject = $this->makeSubject($submittedData, $metaData);
        $this->expectException(InvalidFormIdException::class);
        $subject->validate();
    }
}
