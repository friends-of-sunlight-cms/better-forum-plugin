<?php

namespace SunlightExtend\BetterForum;

use Fosc\Feature\Plugin\Config\FieldGenerator;
use Sunlight\Plugin\Action\ConfigAction as BaseConfigAction;
use Sunlight\Util\ConfigurationFile;
use Sunlight\Util\Form;

class ConfigAction extends BaseConfigAction
{
    protected function getFields(): array
    {
        $config = $this->plugin->getConfig();

        return [
            'show_icon_panel' => [
                'label' => _lang('betterforum.config.show_icon_panel'),
                'input' => Form::input('checkbox', 'config[show_icon_panel]', '1', ['checked' => Form::loadCheckbox('config', $config['show_icon_panel'], 'show_icon_panel')]),
                'type' => 'checkbox'
            ],
            'show_topics' => [
                'label' => _lang('betterforum.config.show_topics'),
                'input' => Form::input('checkbox', 'config[show_topics]', '1', ['checked' => Form::loadCheckbox('config', $config['show_topics'], 'show_topics')]),
                'type' => 'checkbox'
            ],
            'show_answers' => [
                'label' => _lang('betterforum.config.show_answers'),
                'input' => Form::input('checkbox', 'config[show_answers]', '1', ['checked' => Form::loadCheckbox('config', $config['show_answers'], 'show_answers')]),
                'type' => 'checkbox'
            ],
            'show_latest' => [
                'label' => _lang('betterforum.config.show_latest'),
                'input' => Form::input('checkbox', 'config[show_latest]', '1', ['checked' => Form::loadCheckbox('config', $config['show_latest'], 'show_latest')]),
                'type' => 'checkbox'
            ],
            'show_latest_answers' => [
                'label' => _lang('betterforum.config.show_latest_answers'),
                'input' => Form::input('checkbox', 'config[show_latest_answers]', '1', ['checked' => Form::loadCheckbox('config', $config['show_latest_answers'], 'show_latest_answers')]),
                'type' => 'checkbox'
            ],
            'pos_latest_answers' => [
                'label' => _lang('betterforum.config.pos_latest_answers'),
                'input' => Form::select('config[pos_latest_answers]', [
                    0 => _lang('betterforum.config.on_top'),
                    1 => _lang('betterforum.config.on_bottom'),
                ], $config['pos_latest_answers'], ['class' => 'inputsmall']),
            ],
        ];
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
