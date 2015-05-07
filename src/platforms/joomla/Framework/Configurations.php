<?php

/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   GNU/GPLv2 and later
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Gantry\Framework;

use Gantry\Admin\ThemeList;
use Gantry\Component\Filesystem\Folder;
use Gantry\Framework\Base\Configurations as BaseConfigurations;
use Gantry\Joomla\StyleHelper;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class Configurations extends BaseConfigurations
{
    /**
     * @param string $path
     * @return $this
     */
    public function load($path = 'gantry-config://')
    {
        $gantry = $this->container;

        $styles = ThemeList::getStyles($gantry['theme.name']);

        $configurations = [];
        foreach ($styles as $style) {
            $preset = isset($style->params['preset']) ? $style->params['preset'] : null;
            $configuration = isset($style->params['configuration']) ? $style->params['configuration'] : $preset;

            if ($configuration && $configuration != $style->id) {
                // New style generated by Joomla.
                StyleHelper::copy($style, $configuration, $style->id);
            }
            $configurations[$style->id] = $style->style;
        }

        asort($configurations);

        $this->items = $this->addDefaults($configurations);

        return $this;
    }

    public function preset($id)
    {
        if (is_numeric($id)) {
            $style = StyleHelper::getStyle($id);
            $params = json_decode($style->params, true);

            $id = isset($params['preset']) ? $params['preset'] : 'default';
        }

        return $id;
    }

    public function current($template = null)
    {
        if (!$template) {
            // Get the template.
            $template = \JFactory::getApplication()->getTemplate(true);
        }

        $gantry = $this->container;
        $locator = $gantry['locator'];

        $preset = $template->params->get('preset', 'default');
        $configuration = $template->params->get('configuration', $template->id);

        return is_dir($locator("gantry-config://{$configuration}")) ? $configuration : $preset;
    }

    public function duplicate($id)
    {
        $model = StyleHelper::loadModel();

        if (!$model->duplicate($id)) {
            throw new \RuntimeException($model->getError(), 400);
        }
    }

    public function rename($id, $title)
    {
        $model = StyleHelper::loadModel();

        $item = $model->getTable();
        $item->load($id);

        if (!$item->id) {
            throw new \RuntimeException('Configuration not found', 404);
        }

        $item->title = $title;

        if (!$item->check()) {
            throw new \RuntimeException($item->getError(), 400);
        }

        if (!$item->store()) {
            throw new \RuntimeException($item->getError(), 500);
        }
    }

    public function delete($id)
    {
        $model = StyleHelper::loadModel();

        if (!$model->delete($id)) {
            throw new \RuntimeException($model->getError(), 400);
        }
    }
}
