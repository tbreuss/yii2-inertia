<?php

namespace tebe\inertia;

use Yii;
use yii\base\Application;
use yii\base\Component;
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
    public $view = '@inertia/views/inertia';

    /**
     * @inheritDoc
     */
    public function init()
    {
        Yii::setAlias('@inertia', __DIR__);

        // Unset header since at least yii\web\ErrorAction is testing it
        // Yii::$app->request->headers->set('X-Requested-With', null);

        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, [$this, 'handleResponse']);
    }

    /**
     * @param $event
     */
    public function handleResponse($event)
    {
        /** @var Request $request */
        $request = Yii::$app->request;
        $method = $request->getMethod();

        /** @var Response $response */
        $response = $event->sender;

        if (!$request->headers->has('X-Inertia')) {
            return;
        }

        if ($response->isOk) {
            $response->format = Response::FORMAT_JSON;
            $response->headers->set('Vary', 'Accept');
            $response->headers->set('X-Inertia', 'true');
        }

        if ($method === 'GET') {
            if ($request->headers->has('X-Inertia-Version')) {
                $version = $request->headers->get('X-Inertia-Version', null, true);
                if ($version !== $this->getVersion()) {
                    $response->setStatusCode(409);
                    $response->headers->set('X-Inertia-Location', $request->getAbsoluteUrl());
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

        if ($response->headers->has('X-Redirect')) {
            $url = $response->headers->get('X-Redirect', null, true);
            $response->headers->set('Location', $url);
            $response->headers->set('X-Redirect', null);
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
     * @param array $params
     */
    public function share(array $params = [])
    {
        Yii::$app->params[$this->shareKey] = $params;
    }

    /**
     * @return array
     */
    public function getShared()
    {
        $shared = [];
        if (isset(Yii::$app->params[$this->shareKey])) {
            $shared = Yii::$app->params[$this->shareKey];
        }
        return $shared;
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
