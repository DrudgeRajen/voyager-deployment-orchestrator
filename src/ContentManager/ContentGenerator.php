<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager;

class ContentGenerator
{
    /** @var string */
    public $indentCharacter = '    ';

    /** @var string */
    private $newLineCharacter = PHP_EOL;

    /**
     * Format Content.
     *
     * @param $array
     * @param bool $indexed
     *
     * @return mixed|null|string|string[]
     */
    public function formatContent($array, $indexed = true)
    {
        $content = ($indexed)
            ? var_export($array, true)
            : preg_replace("/[0-9]+ \=\>/i", '', var_export($array, true));
        $lines = explode("\n", $content);
        $inString = false;
        $tabCount = 4;
        for ($i = 1; $i < count($lines); $i++) {
            $lines[$i] = ltrim($lines[$i]);
            //Check for closing bracket
            if (strpos($lines[$i], ')') !== false) {
                $tabCount--;
            }
            //Insert tab count
            if ($inString === false) {
                for ($j = 0; $j < $tabCount; $j++) {
                    $lines[$i] = substr_replace($lines[$i], $this->indentCharacter, 0, 0);
                }
            }
            for ($j = 0; $j < strlen($lines[$i]); $j++) {
                //skip character right after an escape \
                if ($lines[$i][$j] == '\\') {
                    $j++;
                } //check string open/end
                elseif ($lines[$i][$j] == '\'') {
                    $inString = ! $inString;
                }
            }
            //check for openning bracket
            if (strpos($lines[$i], '(') !== false) {
                $tabCount++;
            }
        }
        $content = implode("\n", $lines);

        return $content;
    }


    /**
     * Get Delete Statement.
     *
     * @param $dataArray
     *
     * @return string
     */
    public function getDeleteStatement($dataType): string
    {
        $delete = <<<'TXT'
\$dataType = DataType::find('10');

            if (is_bread_translatable(\$dataType)) {
                \$dataType->deleteAttributeTranslations(\$dataType->getTranslatableAttributes());
            }

            if (\$dataType) {
                \$dataType->destroy(10);
            }
TXT;
        return $delete;
    }

    /**
     * Generate Menu Delete Statements.
     *
     * @param $dataType
     * @return string
     */
    public function generateMenuDeleteStatements($dataType) : string
    {
        $menuDelete = <<<TXT
\$menuItem = MenuItem::where('route', 'voyager.\$dataType->slug.index');

        if (\$menuItem->exists()) {
        \$menuItem->delete();
        }
TXT;
        return $menuDelete;
    }

    /**
     * Get Permission Statements.
     *
     * @param $dataType
     * @param null $type
     *
     * @return string
     */
    public function getPermissionStatement($dataType, $type = null) : string
    {
        $permission = <<<'TXT'
Voyager::model('Permission')->generateFor(\$dataType->name);
TXT;

        if (! is_null($type)) {
            $permission = <<<TXT
Voyager::model('Permission')->removeFrom(\$dataType->name);
TXT;
        }

        return $permission;
    }

    /**
     * Get Menu Insert Statements.
     *
     * @param $dataType
     * @return string
     */
    public function getMenuInsertStatements($dataType) : string
    {
        $menu = <<<'TXT'
\$menu = Menu::where('name', config('voyager.bread.default_menu'))->firstOrFail();

            \$menuItem = MenuItem::firstOrNew([
                'menu_id' => \$menu->id,
                'title' => 'Credit Cards',
                'url' => '',
                'route' => 'voyager.credit-cards.index',
            ]);

            \$order = Voyager::model('MenuItem')->highestOrderMenuItem();

            if (!\$menuItem->exists) {
                \$menuItem->fill([
                    'target' => '_self',
                    'icon_class' => '',
                    'color' => null,
                    'parent_id' => null,
                    'order' => \$order,
                ])->save();
            }
TXT;

        return $menu;
    }

    /**
     * Generate Orchestra Seeder Content.
     *
     * @param $className
     * @param $content
     *
     * @return mixed|null|string|string[]
     */
    public function generateOrchestraSeederContent($className, $content)
    {
        if (strpos($className, FilesGenerator::DELETED_SEEDER_SUFFIX) !== false) {
            $toBeDeletedClassName = strstr(
                $className,
                FilesGenerator::DELETED_SEEDER_SUFFIX,
                true
            );
            $breadTypeAddedClass = $toBeDeletedClassName . FilesGenerator::TYPE_SEEDER_SUFFIX;

            $breadRowAddedClass = $toBeDeletedClassName . FilesGenerator::ROW_SEEDER_SUFFIX;

            $content = str_replace("\$this->seed({$breadTypeAddedClass}::class);", '', $content);
            $content = str_replace("\$this->seed({$breadRowAddedClass}::class);", '', $content);
        }

        if (strpos($content, "\$this->seed({$className}::class)") === false) {
            if (
                strpos($content, '#orchestraseeder_start') &&
                strpos($content, '#orchestraseeder_end') &&
                strpos($content, '#orchestraseeder_start') < strpos($content, '#orchestraseeder_end')
            ) {
                $content = preg_replace(
                    "/(\#orchestraseeder_start.+?)(\#orchestraseeder_end)/us",
                    "$1\$this->seed({$className}::class);{$this->newLineCharacter}{$this->indentCharacter}{$this->indentCharacter}$2",
                    $content
                );
            } else {
                $content = preg_replace(
                    "/(run\(\).+?)}/us",
                    "$1{$this->indentCharacter}\$this->seed({$className}::class);{$this->newLineCharacter}{$this->indentCharacter}}",
                    $content
                );
            }
        }

        return $content;
    }
}
