<?php

namespace tebe\inertia\web;

use Yii;

class Request extends \yii\web\Request
{
    const CSRF_HEADER = 'X-XSRF-TOKEN';

    public $csrfParam = 'XSRF-TOKEN';

    public $csrfCookie = ['httpOnly' => false];

    public function init()
    {
        $this->parsers['application/json'] = 'yii\web\JsonParser';
    }

    /**
     * @return string the CSRF token sent via [[CSRF_HEADER]] by browser. Null is returned if no such header is sent.
     */
    public function getCsrfTokenFromHeader()
    {
        $token = $this->headers->get(static::CSRF_HEADER);

        $data = Yii::$app->getSecurity()->validateData($token, $this->cookieValidationKey);
        if ($data === false) {
            return null;
        }

        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70000) {
            $data = @unserialize($data, ['allowed_classes' => false]);
        } else {
            $data = @unserialize($data);
        }

        return Yii::$app->security->maskToken($data[1]);
    }
}
