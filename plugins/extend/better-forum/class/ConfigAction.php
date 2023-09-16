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
                'input' => '<input type="checkbox" name="config[show_icon_panel]" value="1"' . Form::activateCheckbox($config['show_icon_panel']) . '>',
                'type' => 'checkbox'
            ],
            'show_topics' => [
                'label' => _lang('betterforum.config.show_topics'),
                'input' => '<input type="checkbox" name="config[show_topics]" value="1"' . Form::activateCheckbox($config['show_topics']) . '>',
                'type' => 'checkbox'
            ],
            'show_answers' => [
                'label' => _lang('betterforum.config.show_answers'),
                'input' => '<input type="checkbox" name="config[show_answers]" value="1"' . Form::activateCheckbox($config['show_answers']) . '>',
                'type' => 'checkbox'
            ],
            'show_latest' => [
                'label' => _lang('betterforum.config.show_latest'),
                'input' => '<input type="checkbox" name="config[show_latest]" value="1"' . Form::activateCheckbox($config['show_latest']) . '>',
                'type' => 'checkbox'
            ],
            'show_latest_answers' => [
                'label' => _lang('betterforum.config.show_latest_answers'),
                'input' => '<input type="checkbox" name="config[show_latest_answers]" value="1"' . Form::activateCheckbox($config['show_latest_answers']) . '>',
                'type' => 'checkbox'
            ],
            'pos_latest_answers' => [
                'label' => _lang('betterforum.config.pos_latest_answers'),
                'input' => _buffer(function () use ($config) { ?>
                    <select name="config[pos_latest_answers]" class="inputsmall">
                        <option value="0" <?= Form::selectOption($config['pos_latest_answers'] === 0) ?>><?= _lang('betterforum.config.on_top') ?></option>
                        <option value="1" <?= Form::selectOption($config['pos_latest_answers'] === 1) ?>><?= _lang('betterforum.config.on_bottom') ?></option>
                    </select>
                <?php }),
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
