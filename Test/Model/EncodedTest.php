<?php
/**
 * Created by Ringier Axel Springer Tech
 * Date: 24/11/2022
 *
 * @category    MaciejBuchert
 * @package     MaciejBuchert_CaptchaBase64Encoding
 * @author      Maciej Buchert <maciej.buchert@ringieraxelspringer.com>
 */

namespace MaciejBuchert\CaptchaBase64Encoding\Test\Model;

use MaciejBuchert\CaptchaBase64Encoding\Model\Encoded;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\ResourceModel\Log;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\Storage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class EncodedTest
 * @package MaciejBuchert\CaptchaBase64Encoding\Test\Model
 */
class EncodedTest extends TestCase
{
    /**
     * Expiration frame
     */
    const EXPIRE_FRAME = 86400;

    /**
     * Captcha default config data
     * @var array
     */
    protected static $_defaultConfig = [
        'type' => 'default',
        'enable' => '1',
        'font' => 'linlibertine',
        'mode' => 'after_fail',
        'forms' => 'user_forgotpassword,user_create',
        'failed_attempts_login' => '3',
        'failed_attempts_ip' => '1000',
        'timeout' => '7',
        'length' => '4-5',
        'symbols' => 'ABCDEFGHJKMnpqrstuvwxyz23456789',
        'case_sensitive' => '0',
        'shown_to_logged_in_user' => ['contact_us' => 1],
        'always_for' => [
            'user_create' => '1',
            'user_forgotpassword' => '1',
            'contact_us' => '1'
        ],
    ];

    /**
     * @var MockObject
     */
    protected $_dirMock;

    /**
     * path to fonts
     * @var array
     */
    protected $_fontPath = [
        'LinLibertine' => [
            'label' => 'LinLibertine',
            'path' => 'lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf',
        ],
    ];

    /**
     * @var Encoded
     */
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_objectManager;

    /**
     * @var MockObject
     */
    protected $_storeManager;

    /**
     * @var MockObject
     */
    protected $session;

    /**
     * @var MockObject|LogFactory
     */
    protected $_resLogFactory;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->session = $this->_getSessionStub();

        $this->_storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->_storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            $this->_getStoreStub()
        );

        // \Magento\Customer\Model\Session
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->willReturnMap(
            [
                Data::class => $this->_getHelperStub(),
                Session::class => $this->session,
            ]
        );

        $this->_resLogFactory = $this->createPartialMock(
            LogFactory::class,
            ['create']
        );
        $this->_resLogFactory->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $this->_getResourceModelStub()
        );

        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('random-string');

        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);

        $this->_object = new Encoded(
            $this->session,
            $this->_getHelperStub(),
            $this->_resLogFactory,
            'user_create',
            $randomMock,
            $this->userContextMock
        );
    }

    /**
     * Create stub session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSessionStub()
    {
        $helper = new ObjectManager($this);
        $sessionArgs = $helper->getConstructArguments(
            Session::class,
            ['storage' => new Storage()]
        );
        $session = $this->getMockBuilder(Session::class)
            ->setMethods(['isLoggedIn', 'getUserCreateWord'])
            ->setConstructorArgs($sessionArgs)
            ->getMock();
        $session->expects($this->any())->method('isLoggedIn')->willReturn(false);

        $session->setData(
            [
                'user_create_word' => [
                    'data' => 'AbCdEf5',
                    'words' => 'AbCdEf5',
                    'expires' => time() + self::EXPIRE_FRAME
                ]
            ]
        );
        return $session;
    }

    /**
     * Create helper stub
     * @return Data
     */
    protected function _getHelperStub()
    {
        $helper = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['getConfig', 'getFonts', '_getWebsiteCode', 'getImgUrl']
            )->getMock();

        $helper->expects(
            $this->any()
        )->method(
            'getConfig'
        )->willReturnCallback(
            'Magento\Captcha\Test\Unit\Model\DefaultTest::getConfigNodeStub'
        );

        $helper->expects($this->any())->method('getFonts')->willReturn($this->_fontPath);

        $helper->expects($this->any())->method('_getWebsiteCode')->willReturn('base');

        $helper->expects(
            $this->any()
        )->method(
            'getImgUrl'
        )->willReturn(
            'http://localhost/media/captcha/base/'
        );

        return $helper;
    }

    /**
     * Get stub for resource model
     * @return Log
     */
    protected function _getResourceModelStub()
    {
        $resourceModel = $this->createPartialMock(
            Log::class,
            ['countAttemptsByRemoteAddress', 'countAttemptsByUserLogin', 'logAttempt', '__wakeup']
        );

        $resourceModel->expects($this->any())->method('logAttempt');

        $resourceModel->expects($this->any())->method('countAttemptsByRemoteAddress')->willReturn(0);

        $resourceModel->expects($this->any())->method('countAttemptsByUserLogin')->willReturn(3);
        return $resourceModel;
    }

    /**
     * Mock get config method
     * @static
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getConfigNodeStub()
    {
        $args = func_get_args();
        $hashName = $args[0];

        if (array_key_exists($hashName, self::$_defaultConfig)) {
            return self::$_defaultConfig[$hashName];
        }

        throw new \InvalidArgumentException('Unknow id = ' . $hashName);
    }

    /**
     * Create store stub
     *
     * @return Store
     */
    protected function _getStoreStub()
    {
        $store = $this->getMockBuilder(Store::class)
            ->addMethods(['isAdmin'])
            ->onlyMethods(['getBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getBaseUrl')->willReturn('http://localhost/media/');
        $store->expects($this->any())->method('isAdmin')->willReturn(false);
        return $store;
    }

    /**
     * @return array
     */
    public function isShownToLoggedInUserDataProvider()
    {
        return [
            [true, 'contact_us'],
            [false, 'user_create'],
            [false, 'user_forgotpassword']
        ];
    }

    /**
     * @return array
     */
    public function generateWordProvider()
    {
        return [
            ['ABC123'],
            ['1234567890'],
            ['The quick brown fox jumps over the lazy dog.']
        ];
    }

    /**
     * @covers Encoded::getImgSrc
     */
    public function testGetImgSrc(): void
    {
        $this->_object->generate();
        $this->assertEquals(
            $this->_object->getImgSrc(),
            'http://localhost/media/captcha/base/' . $this->_object->getId() . '.png'
        );
    }

    /**
     * @covers Encoded::generate
     */
    public function testGenerate(): void
    {
        $this->assertEquals(
            $this->_object->generate(),
            $this->_object->getId()
        );
    }
}
