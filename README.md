# Magento Captcha image generation replacement
For unknown reason, Magento Captcha module generate and save images 
for forms like registration, login or contact page. That takes up the disk
and does not clean it.

That module replaces image generation with base64 encoding, 
so image is generated but not saved

Module has been created with Magento 2.4.5, 
but it could work with other versions

#How to install

1. Download the newest release
2. Extract it to `app/code/MaciejBuchert/CaptchaBase64Encoding` directory
3. Execute commands 
   1. `php bin/magento module:enable MaciejBuchert_CaptchaBase64Encoding`
   2. `php bin/magento setup:upgrade`
   3. `php bin/magento setup:di:compile`
   4. `php bin/magento cache:flush`
