use Exception;

<?php

namespace app\components\i18n;


class MissingLocalizedAttributeException extends \Exception
{
    /**
     * MissingLocalizedAttributeException constructor.
     *
     * @param string $attribute The missing attribute name.
     * @param int $code The exception code (optional).
     * @param Exception|null $previous The previous exception used for exception chaining (optional).
     */
    public function __construct(string $attribute, int $code = 0, $previous = null)
    {
        $message = "The localized attribute '{$attribute}' is missing.";
        parent::__construct($message, $code, $previous);
    }
}
