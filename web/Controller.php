<?php

namespace tebe\inertia\web;

use Yii;

class Controller extends \yii\web\Controller
{
    /**
     * @param string $component
     * @param array $params
     * @return array|string
     * @throws \yii\base\InvalidConfigException
     */
    public function inertia($component, $params = [])
    {
        $params = [
            'component' => $component,
            'props' => $this->getInertiaProps($params),
            'url' => $this->getInertiaUrl(),
            'version' => $this->getInertiaVersion()
        ];

        if (Yii::$app->request->headers->has('X-Inertia')) {
            return $params;
        }

        $view = Yii::$app->get('inertia')->view;
        return $this->render($view, [
            'page' => $params
        ]);
    }

    /**
     * @param array $params
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getInertiaProps($params = [])
    {
        return array_merge(
            Yii::$app->get('inertia')->getShared(),
            $params
        );
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function getInertiaUrl()
    {
        $url = Yii::$app->request->getUrl();
        return $url;
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function getInertiaVersion()
    {
        return Yii::$app->get('inertia')->getVersion();
    }
}
