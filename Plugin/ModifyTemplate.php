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

use MaciejBuchert\CaptchaBase64Encoding\Model\Encoded;
use Magento\Captcha\Block\Captcha\DefaultCaptcha;

/**
 * Class ModifyTemplate
 * @package MaciejBuchert\CaptchaBase64Encoding\Plugin
 */
class ModifyTemplate
{
    /**
     * @param DefaultCaptcha $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetTemplate(DefaultCaptcha $subject, callable $proceed): string
    {
        if ($subject->getCaptchaModel() instanceof Encoded) {
            return $subject->getIsAjax() ? '' : 'MaciejBuchert_CaptchaBase64Encoding::default.phtml';
        }
        return $proceed();
    }
}
