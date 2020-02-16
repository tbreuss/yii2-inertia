<?php

namespace tebe\inertia\web;

use Yii;

class Controller extends \yii\web\Controller
{
    public function inertia($component, $params = [])
    {
        $params = [
            'component' => $component,
            'props' => $this->getProps($params),
            'url' => $this->getUrl(),
            'version' => $this->getVersion()
        ];

        if (Yii::$app->request->headers->has('X-Inertia')) {
            return $params;
        }

        $view = Yii::$app->get('inertia')->view;
        return $this->render($view, [
            'page' => $params
        ]);
    }

    private function getProps($params = [])
    {
        return array_merge(
            Yii::$app->get('inertia')->getShared(),
            $params
        );
    }

    private function getUrl()
    {
        $url = Yii::$app->request->getUrl();
        return $url;
    }

    private function getVersion()
    {
        return Yii::$app->get('inertia')->getVersion();
    }
}
