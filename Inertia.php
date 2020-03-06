<?php

namespace tebe\inertia;

use Yii;
use yii\base\Application;
use yii\base\Component;
use yii\base\Event;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

class Inertia extends Component
{
    /** @var array */
    public $assetsDirs = [
        '@webroot/assets'
    ];

    /** @var string */
    public $shareKey = '__inertia__';

    /** @var string */
    public $view = '@tebe/inertia/views/inertia';

    /**
     * @inheritDoc
     */
    public function init()
    {
        // Unset header since at least yii\web\ErrorAction is testing it
        // Yii::$app->request->headers->set('X-Requested-With', null);

        Yii::$app->on(Application::EVENT_AFTER_REQUEST, [$this, 'applicationAfterRequestHandler']);
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, [$this, 'responseBeforeSendHandler']);
    }

    /**
     * @param Event $event
     */
    public function applicationAfterRequestHandler($event)
    {
        $response = Yii::$app->getResponse();
        if ($response->headers->has('X-Redirect')) {
            $url = $response->headers->get('X-Redirect', null, true);
            $response->headers->set('Location', $url);
        }
    }

    /**
     * @param Event $event
     */
    public function responseBeforeSendHandler($event)
    {
        $request = Yii::$app->getRequest();
        $method = $request->getMethod();

        /** @var Response $response */
        $response = $event->sender;

        if (!$request->headers->has('X-Inertia')) {
            if ($request->enableCsrfValidation) {
                $request->getCsrfToken(true);
            }
            return;
        }

        if ($response->isOk) {
            $response->format = Response::FORMAT_JSON;
            $response->headers->set('X-Inertia', 'true');
        }

        if ($method === 'GET') {
            if ($request->headers->has('X-Inertia-Version')) {
                $version = $request->headers->get('X-Inertia-Version', null, true);
                if ($version !== $this->getVersion()) {
                    $response->setStatusCode(409);
                    $response->headers->set('X-Inertia-Location', $request->getAbsoluteUrl());
                    return;
                }
            }
        }

        if ($response->getIsRedirection()) {
            if ($response->getStatusCode() === 302) {
                if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
                    $response->setStatusCode(303);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $hashes = [];
        foreach ($this->assetsDirs as $assetDir) {
            $hashes[] = $this->hashDirectory(Yii::getAlias($assetDir));
        }
        return md5(implode('', $hashes));
    }

    /**
     * @param array|string $key
     * @param array/null $value
     */
    public function share($key, $value = null)
    {
        if (is_array($key)) {
            Yii::$app->params[$this->shareKey] = array_merge($this->getShared(), $key);
        } elseif (is_string($key) && is_array($value)) {
            Yii::$app->params[$this->shareKey] = array_merge($this->getShared(), [$key => $value]);
        }
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function getShared($key = null)
    {
        if (is_string($key) && isset(Yii::$app->params[$this->shareKey][$key])) {
            return Yii::$app->params[$this->shareKey][$key];
        }
        if (isset(Yii::$app->params[$this->shareKey])) {
            return Yii::$app->params[$this->shareKey];
        }
        return [];
    }

    /**
     * Generate an MD5 hash string from the contents of a directory.
     *
     * @param string $directory
     * @return boolean|string
     * @todo optimize by using webpack build info or a cache
     */
    private function hashDirectory($directory)
    {
        $files = array();
        $dir = dir($directory);
        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (is_dir($directory . '/' . $file)) {
                    $files[] = $this->hashDirectory($directory . '/' . $file);
                } else {
                    $files[] = md5_file($directory . '/' . $file);
                }
            }
        }
        $dir->close();
        return md5(implode('', $files));
    }

}
