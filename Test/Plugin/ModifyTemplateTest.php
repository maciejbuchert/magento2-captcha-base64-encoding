<?php
/**
 * Date: 23/12/2022
 *
 * @package     MaciejBuchert_CaptchaBase64Encoding
 * @author      Maciej Buchert <maciej@buchert.pl>
 * @copyright   2022-2072 Maciej Buchert
 * @license     OSL-3.0, AFL-3.0
 */

namespace MaciejBuchert\CaptchaBase64Encoding\Test\Plugin;

use MaciejBuchert\CaptchaBase64Encoding\Model\Encoded;
use MaciejBuchert\CaptchaBase64Encoding\Plugin\ModifyTemplate;
use Magento\Captcha\Block\Captcha\DefaultCaptcha;
use PHPUnit\Framework\TestCase;

/**
 * Class ModifyTemplateTest
 * @package MaciejBuchert\CaptchaBase64Encoding\Test\Plugin
 */
class ModifyTemplateTest extends TestCase
{
    /**
     * Test returned template when Encoded Captcha Model is provided
     */
    public function testSettingTemplateForEncodedCaptchaModel(): void
    {
        $encodedCaptchaMock = $this->createMock(Encoded::class);

        $defaultCaptchaMock = $this->createMock(DefaultCaptcha::class);
        $defaultCaptchaMock
            ->method('getCaptchaModel')
            ->willReturn($encodedCaptchaMock);

        $proceed = static function () use ($defaultCaptchaMock) {
            return $defaultCaptchaMock->getTemplate();
        };

        $plugin = new ModifyTemplate();

        $result = $plugin->aroundGetTemplate($defaultCaptchaMock, $proceed);

        $this->assertEquals('MaciejBuchert_CaptchaBase64Encoding::default.phtml', $result);
    }

    /**
     * Test returned template when Default Captcha Model is provided
     */
    public function testSettingTemplateForDefaultCaptchaModel(): void
    {
        $defaultCaptchaMock = $this->createMock(DefaultCaptcha::class);
        $defaultCaptchaMock
            ->method('getTemplate')
            ->willReturn('Magento_Captcha::default.phtml');

        $proceed = static function () use ($defaultCaptchaMock) {
            return $defaultCaptchaMock->getTemplate();
        };

        $plugin = new ModifyTemplate();

        $result = $plugin->aroundGetTemplate($defaultCaptchaMock, $proceed);

        $this->assertEquals('Magento_Captcha::default.phtml', $result);
    }
}
