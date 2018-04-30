<?php

namespace OS\LocalCaptcha;


class FormMetaData implements \JsonSerializable
{
    const DATETIME_FORMAT = \DateTime::ISO8601;

    /**
     * @var string
     */
    private $formId;

    /**
     * @var \DateTimeImmutable
     */
    private $generationDate;

    /**
     * @var string[]
     */
    private $honeypots;

    /**
     * FormMetaData constructor.
     *
     * @param string $formId
     * @param \DateTimeImmutable $generationDate
     * @param string[] $honeypots
     */
    public function __construct(string $formId, \DateTimeImmutable $generationDate, array $honeypots)
    {
        $this->formId = $formId;
        $this->generationDate = $generationDate;
        $this->honeypots = $honeypots;
    }

    /**
     * Creates a new FormMetaData instance from the json representation
     *
     * @param array $jsonData
     *
     * @return FormMetaData
     */
    public static function createFromArray(array $jsonData): FormMetaData
    {
        return new FormMetaData(
            $jsonData['i'],
            \DateTimeImmutable::createFromFormat(static::DATETIME_FORMAT, $jsonData['d']),
            $jsonData['h']
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'i' => $this->formId,
            'd' => $this->generationDate->format(static::DATETIME_FORMAT),
            'h' => $this->honeypots
        ];
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return $this->formId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getGenerationDate(): \DateTimeImmutable
    {
        return $this->generationDate;
    }

    /**
     * @return string[]
     */
    public function getHoneypots(): array
    {
        return $this->honeypots;
    }
}
