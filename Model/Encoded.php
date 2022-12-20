<?php
/**
 * Date: 23/12/2022
 *
 * @package     MaciejBuchert_CaptchaBase64Encoding
 * @author      Maciej Buchert <maciej@buchert.pl>
 * @copyright   2022-2072 Maciej Buchert
 * @license     OSL-3.0, AFL-3.0
 */


namespace MaciejBuchert\CaptchaBase64Encoding\Model;

use Magento\Captcha\Model\DefaultModel;

/**
 * Class Encoded
 * @package MaciejBuchert\CaptchaBase64Encoding\Model
 */
class Encoded extends DefaultModel
{
    /**
     * @var string|null
     */
    protected ?string $encoded_img;

    /**
     * @return string
     */
    public function getImgSrc(): string
    {
        return sprintf('data:image/png;base64, %s', $this->encoded_img);
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        parent::generate();

        $imagePath = sprintf('%s%s%s', $this->getImgDir(), $this->getId(), $this->getSuffix());

        $image = file_get_contents($imagePath);
        $this->encoded_img = base64_encode($image);

        unlink($imagePath);

        return $this->getId();
    }
}
