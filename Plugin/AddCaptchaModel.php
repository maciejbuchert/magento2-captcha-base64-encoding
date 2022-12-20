<?php
/**
 * Date: 23/12/2022
 *
 * @package     MaciejBuchert_CaptchaBase64Encoding
 * @author      Maciej Buchert <maciej@buchert.pl>
 * @copyright   2022-2072 Maciej Buchert
 * @license     OSL-3.0, AFL-3.0
 */

namespace MaciejBuchert\CaptchaBase64Encoding\Plugin;

use InvalidArgumentException;
use Magento\Captcha\Model\CaptchaFactory;
use Magento\Captcha\Model\CaptchaInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class AddCaptchaModel
 * @package MaciejBuchert\CaptchaBase64Encoding\Plugin
 */
class AddCaptchaModel
{
    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param CaptchaFactory $subject
     * @param callable $proceed
     * @param string $captchaType
     * @param string $formId
     * @return CaptchaInterface
     * @return CaptchaInterface
     */
    public function aroundCreate(
        CaptchaFactory $subject,
        callable $proceed,
        string $captchaType,
        string $formId
    ): CaptchaInterface {
        if ($captchaType === 'Encoded' || $captchaType === 'encoded') {
            $className = 'MaciejBuchert\CaptchaBase64Encoding\Model\\' . ucfirst($captchaType);

            $instance = $this->objectManager->create($className, ['formId' => $formId]);
            if (!$instance instanceof CaptchaInterface) {
                throw new InvalidArgumentException(
                    $className . ' does not implement \Magento\Captcha\Model\CaptchaInterface'
                );
            }
            return $instance;
        }

        return $proceed($captchaType, $formId);
    }
}
