<?php
namespace app\gii\routeViewer;

use Yii;
use yii\gii\Generator;

class RouteViewerGenerator extends Generator
{
    public function getName()
    {
        return 'Route Viewer';
    }

    public function getDescription()
    {
        return 'Displays a list of all registered URL routes.';
    }

    public function generate()
    {
        return [];
    }

    public function formView()
    {
        return '@app/app/gii/routeViewer/views/form.php';
    }

    // public function getTemplates()
    // {
    //     return [
    //         'default' => '@app/gii/routeViewer/templates/default',
    //     ];
    // }

    // public function rules()
    // {
    //     return [
    //         [['name'], 'string'],
    //         [['name'], 'required'],
    //     ];
    // }

    // public function attributeLabels()
    // {
    //     return [
    //         'name' => 'Name',
    //     ];
    // }
}
