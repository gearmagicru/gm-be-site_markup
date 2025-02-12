<?php
/**
 * Этот файл является частью пакета GM Panel.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\SiteMarkup\Model;

use Gm;
use Gm\Theme\Theme;
use Gm\Data\Model\RecordModel;
use Gm\Filesystem\Filesystem as Fs;

/**
 * Модель фрагмента текста разметки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Panel\Data\Model
 * @since 1.0
 */
class MarkupBlock extends RecordModel
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_SAVE, function ($isInsert, $columns, $result) {
                /** @var \Gm\Panel\Http\Response $response */
                $response = $this->response();

                if ($result) {
                    // всплывающие сообщение
                    $response
                        ->meta
                            ->cmdPopupMsg(
                                $this->module->t('Block "{0}" successfully changed', [$this->title]), 
                                $this->t('Saving a block'), 
                                'accept'
                            );
                }
                // обновить фрейм
                $response
                    ->meta
                        ->cmdComponent($this->module->viewId('frame'), 'reload');
            });
    }
        
    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'         => 'id', // идентификатор фрагмента (указывается в `beginMarkup('id')`)
            'html'       => 'html', // текст фрагмента
            'title'      => 'title', // заголовок фрагмента
            'calledFrom' => 'calledFrom' // имя файла шаблона
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['id', 'calledFrom'], 'notEmpty']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isNewRecord(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateProcess(array $attributes = null): false|int
    {
        if (!$this->beforeSave(false)) {
            return false;
        }

        // возвращает атрибуты без псевдонимов (если они были указаны)
        $attributes = $this->unmaskedAttributes($this->attributes);

        $this->beforeUpdate($attributes);

        // сохранение фрагмента текста 
        $result = $this->saveBlock($attributes);
        $this->afterSave(false, $attributes, $result);
        return $result;
    }

    /**
     * Сохраняет фрагмент текста в шаблон.
     * 
     * @param array $attributes Параметры (атрибуты) фрагмента.
     * 
     * @return bool Возвращает занчение `true`, если фрагмент текста сохранён в шаблон.
     * 
     * @throws \Gm\Filesystem\Exception\FileNotFoundException
     */
    public function saveBlock(array $attributes): bool
    {
        $filename = $this->getViewFile();
        $content = Fs::get($filename);

        // получение фрагмента из файла шаблона
        $script = $this->getScriptPhpFromView($attributes['id'], $content);
        if ($script === null) {
            $this->addError($this->t('Error getting a mutable fragment from a template (fragment selected incorrectly)'));
            return false;
        }

        // замена фрагмента на новый
        $content = str_replace($script['content'], "\n" . $attributes['html'], $content);

        // запись нового фрагмента в файл
        if (Fs::put($filename, $content) === false) {
            $this->addError($this->module->t('Error writing text fragment to template "{0}"', [$filename]));
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            // проверка существования шаблона
            $viewFile = $this->getViewFile();
            if (!file_exists($viewFile)) {
                $this->addError(Gm::t('app', 'File "{0}" not found', [$viewFile]));
                return false;
            }
        }
        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function get(mixed $identifier = null): ?static
    {
        return $this;
    }

    /**
     * Возвращает фрагмент текста из шаблона по указанному идентификатору.
     * 
     * @param string $id Идентификатор начала фрагмента текста в шаблоне.
     *     Начало фрагмента в шаблоне `<?= $this->beginMarkup('id') ?>`.
     *     Конец фрагмента в шаблоне `<?= $this->endMarkup() ?>`.
     * @param string $content Текст шаблона.
     * 
     * @return array|null Если значение `null`, ошибка поиска фрагмента. Иначе, фрагмент
     *     текста вида: `['begin' => 0, 'end' => 100, 'content' => '...']`.
     *     Где, 'begin' и 'end' позиция фрагмента текста в шаблоне.
     */
    public function getScriptPhpFromView(string $id, string $content): ?array
    {
        $posOpenTag = mb_strpos($content, '$this->beginMarkup(\'' . $id . '\'');
        if ($posOpenTag === false) return null;

        $posOpenTag = mb_strpos($content, '?>', $posOpenTag);
        if ($posOpenTag === false) return null;

        $posCloseTag = mb_strpos($content, '$this->endMarkup(', $posOpenTag);
        if ($posCloseTag === false) return null;

        $content = mb_substr($content, $posOpenTag + 2, $posCloseTag - $posOpenTag - 2);
        
        $posEndTag = mb_strrpos($content, '<?');
        if ($posEndTag === false) return null;

        $content = mb_substr($content, 0, $posEndTag);
        return [
            'begin'   => $posOpenTag,
            'end'     => $posOpenTag + $posEndTag,
            'content' => $content
        ];
    }

    /**
     * Возвращает имя файла шаблона в соответствии с текущей темой.
     * 
     * @see MarkupBlock::getTheme()
     * 
     * @return string
     */
    protected function getViewFile(): string
    {
        return $this->getTheme()->path . $this->calledFrom;
    }

    /**
     * @var Theme
     */
    protected Theme $theme;

    /**
     * Возвращает текущую тему сайта.
     * 
     * @return Theme
     */
    protected function getTheme(): Theme
    {
        if (!isset($this->theme)) {
            $this->theme = Gm::$app->createFrontendTheme();
            $this->theme->set();
        }
        return $this->theme;
    }
}
