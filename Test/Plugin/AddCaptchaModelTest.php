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
use MaciejBuchert\CaptchaBase64Encoding\Plugin\AddCaptchaModel;
use Magento\Captcha\Model\CaptchaFactory;
use Magento\Captcha\Model\DefaultModel;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AddCaptchaModelTest
 * @package MaciejBuchert\CaptchaBase64Encoding\Test\Plugin
 */
class AddCaptchaModelTest extends TestCase
{
    /**
     * Test returned Captcha Model
     */
    public function testReturnedCaptchaModelWhenDefaultType(): void
    {
        $objectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultCaptchaMock = $this->createMock(DefaultModel::class);
        $objectManager
            ->method('create')
            ->willReturn($defaultCaptchaMock);

        $plugin = new AddCaptchaModel($objectManager);

        $captchaFactoryMock = $this->createMock(CaptchaFactory::class);
        $captchaFactoryMock
            ->method('create')
            ->willReturn($defaultCaptchaMock);

        $proceed = static function ($captchaType, $formId) use ($captchaFactoryMock) {
            return $captchaFactoryMock->create($captchaType, $formId);
        };

        $captchaType = 'defaultModel';
        $formId = 'newsletter';

        $object = $plugin->aroundCreate(
            $captchaFactoryMock,
            $proceed,
            $captchaType,
            $formId
        );

        $this->isInstanceOf(DefaultModel::class)->evaluate($object);
    }

    /**
     * Test returned Captcha Model
     */
    public function testReturnedCaptchaModelWhenEncodedType(): void
    {
        $objectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $encodedCaptchaMock = $this->createMock(Encoded::class);
        $objectManager
            ->method('create')
            ->willReturn($encodedCaptchaMock);

        $plugin = new AddCaptchaModel($objectManager);

        $defaultCaptchaMock = $this->createMock(DefaultModel::class);
        $captchaFactoryMock = $this->createMock(CaptchaFactory::class)
            ->method('create')
            ->willReturn($defaultCaptchaMock);

        $proceed = static function ($captchaType, $formId) use ($captchaFactoryMock) {
            return $captchaFactoryMock->create($captchaType, $formId);
        };

        $captchaType = 'Encoded';
        $formId = 'newsletter';

        $object = $plugin->aroundCreate(
            $captchaFactoryMock,
            $proceed,
            $captchaType,
            $formId
        );

        $this->isInstanceOf(Encoded::class)->evaluate($object);
    }
}
