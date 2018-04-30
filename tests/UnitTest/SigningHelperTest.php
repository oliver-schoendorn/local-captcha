<?php

namespace OS\LocalCaptcha\UnitTest;

use OS\LocalCaptcha\Helper\EncryptionHelper;
use OS\LocalCaptcha\Helper\SigningHelper;
use PHPUnit\Framework\TestCase;

class SigningHelperTest extends TestCase
{
    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;

    /**
     * @var SigningHelper
     */
    private $subject;

    public function setUp()
    {
        $encryptionKey = md5('key');
        $this->encryptionHelper = new EncryptionHelper($encryptionKey);
        $this->subject = new SigningHelper($encryptionKey);

        parent::setUp();
    }

    public function testSign()
    {
        $data = json_encode([ 'foo' => 'bar', md5('lots') => 'of data that', 'might cause' => md5('issues') ]);
        $data = $this->encryptionHelper->encrypt($data);

        $signedData = $this->subject->sign($data);
        verify($signedData)->contains($data);
    }

    public function testGetPayload()
    {
        $data = json_encode([ 'foo' => 'bar', md5('lots') => 'of data that', 'might cause' => md5('issues') ]);
        $data = $this->encryptionHelper->encrypt($data);

        $signedData = $this->subject->sign($data);
        verify($this->subject->getPayload($signedData))->equals($data);
    }

    public function testVerify()
    {
        $data = json_encode([ 'foo' => 'bar', md5('lots') => 'of data that', 'might cause' => md5('issues') ]);
        $data = $this->encryptionHelper->encrypt($data);

        $signedData = $this->subject->sign($data);
        verify($signedData)->contains($data);
        verify($this->subject->verify($signedData))->true();
    }
}
