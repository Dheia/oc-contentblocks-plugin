<?php

namespace DieterHolvoet\ContentBlock\EventListeners;

use Backend\Widgets\Form;
use DieterHolvoet\ContentBlock\Classes\ContainerDefinitionManager;
use DieterHolvoet\ContentBlock\Classes\ContentBlockDefinitionManager;
use DieterHolvoet\ContentBlock\Models\Settings;
use System\Classes\PluginManager;

class BackendFormEventListener
{
    /** @var PluginManager */
    protected $pluginManager;
    /** @var ContentBlockDefinitionManager */
    protected $contentBlockDefinitions;
    /** @var ContainerDefinitionManager */
    protected $containerDefinitions;
    /** @var Settings */
    protected $settings;

    public function __construct(
        PluginManager $pluginManager,
        ContentBlockDefinitionManager $contentBlockDefinitions,
        ContainerDefinitionManager $containerDefinitions,
        Settings $settings
    ) {
        $this->pluginManager = $pluginManager;
        $this->contentBlockDefinitions = $contentBlockDefinitions;
        $this->containerDefinitions = $containerDefinitions;
        $this->settings = $settings;
    }

    public function onExtendFields(Form $widget)
    {
        if (
            $this->pluginManager->hasPlugin('RainLab.Pages')
            && $widget->model instanceof \RainLab\Pages\Classes\Page
            && !$widget->isNested
        ) {
            $this->handleStaticPages($widget);
        }

        if ($widget->model instanceof \Cms\Classes\Page && !$widget->isNested) {
            $this->handleCmsPages($widget);
        }
    }

    protected function handleCmsPages(Form $widget)
    {
        $settings = $widget->model->getAttribute('settings');
        $container = $settings['contentBlockContainer'] ?? $this->settings->getDefaultContainer();
        $containers = $this->containerDefinitions->getDefinitions();
        $groups = $this->contentBlockDefinitions->getFieldGroupsByContainer($container);

        $widget->addTabFields([
            'settings[contentBlockContainer]' => [
                'tab' => 'Content blocks',
                'title' => 'Content block container',
                'type' => 'dropdown',
                'options' => array_map(
                    function (array $definition) { return $definition['label']; },
                    $containers
                ),
            ],
            'contentBlockFields' => [
                'tab' => 'Content blocks',
                'type' => 'repeater',
                'prompt' => 'Add another content block',
                'groups' => $groups,
            ],
        ]);
    }

    protected function handleStaticPages(Form $widget)
    {
        $viewBag = $widget->model->getAttribute('viewBag');
        $container = $viewBag['contentBlockContainer'] ?? $this->settings->getDefaultContainer();
        $containers = $this->containerDefinitions->getDefinitions();
        $groups = $this->contentBlockDefinitions->getFieldGroupsByContainer($container);

        $widget->addTabFields([
            'viewBag[contentBlockContainer]' => [
                'tab' => 'Content blocks',
                'title' => 'Content block container',
                'type' => 'dropdown',
                'options' => array_map(
                    function (array $definition) { return $definition['label']; },
                    $containers
                ),
            ],
            'contentBlockFields' => [
                'tab' => 'Content blocks',
                'type' => 'repeater',
                'prompt' => 'Add another content block',
                'groups' => $groups,
            ],
        ]);
    }
}
