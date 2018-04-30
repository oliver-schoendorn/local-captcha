<?php

namespace OS\LocalCaptcha\UnitTest;

use OS\LocalCaptcha\Helper\EncryptionHelper;
use OS\LocalCaptcha\Helper\Inspector;
use PHPUnit\Framework\TestCase;

class EncryptionHelperTest extends TestCase
{
    /**
     * @var EncryptionHelper
     */
    private $subject;

    /**
     * @var Inspector
     */
    private $inspector;

    /**
     * @throws \ReflectionException
     */
    protected function setUp()
    {
        $this->subject = new EncryptionHelper('some-fancy-key');
        $this->inspector = new Inspector($this->subject);
        parent::setUp();
    }


    public function testConstructor()
    {
        verify($this->inspector->get('encryptionKey'))->notEquals('some-fancy-key');
    }

    public function testEncrypt()
    {
        $testString = json_encode([
            'test' => 'FooBarBaz.BazBarFoo',
            'a lot of data' => hash('sha512', 'that might cause some trouble')
        ]);
        $encryptedString = $this->subject->encrypt($testString);

        verify($testString)->notEquals($encryptedString);
        verify($encryptedString)->notContains($testString);
        verify($encryptedString)->notEquals($this->subject->encrypt($testString));
    }

    public function testDecrypt()
    {
        $testString = json_encode([
            'test' => 'FooBarBaz.BazBarFoo',
            'a lot of data' => hash('sha512', 'that might cause some trouble')
        ]);
        $encryptedString = $this->subject->encrypt($testString);
        $encodedString = base64_encode($encryptedString);

        verify($this->subject->decrypt($encryptedString))->equals($testString);
        verify($this->subject->decrypt(base64_decode($encodedString)))->equals($testString);
    }
}
