<?php

namespace SunlightExtend\BetterForum;

use Fosc\Feature\Plugin\Config\FieldGenerator;
use Sunlight\Plugin\Action\ConfigAction as BaseConfigAction;
use Sunlight\Util\ConfigurationFile;

class ConfigAction extends BaseConfigAction
{
    protected function getFields(): array
    {
        $langPrefix = "%p:betterforum.config";

        $gen = new FieldGenerator($this->plugin);
        $gen->generateField('show_icon_panel', $langPrefix, '%checkbox')
            ->generateField('show_topics', $langPrefix, '%checkbox')
            ->generateField('show_answers', $langPrefix, '%checkbox')
            ->generateField('show_latest', $langPrefix, '%checkbox')
            ->generateField('show_latest_answers', $langPrefix, '%checkbox')
            ->generateField('pos_latest_answers', $langPrefix, '%select', [
                'class' => 'inputsmall',
                'select_options' => [
                    0 => _lang('betterforum.config.on_top'),
                    1 => _lang('betterforum.config.on_bottom'),
                ],
            ]);

        return $gen->getFields();
    }

    protected function mapSubmittedValue(ConfigurationFile $config, string $key, array $field, $value): ?string
    {
        if ($key == 'pos_latest_answers') {
            $config[$key] = (int)$value;
            return null;
        }

        return parent::mapSubmittedValue($config, $key, $field, $value);
    }
}
