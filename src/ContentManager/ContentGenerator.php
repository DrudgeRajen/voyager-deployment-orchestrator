<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager;

class ContentGenerator
{
    /** @var string */
    public $indentCharacter = '    ';

    /** @var string */
    private $newLineCharacter = PHP_EOL;

    /** @var string Data type Delete Statement */
    const DELETE_STATMENT = <<<'TXT'
$dataType = DataType::where('name', '%s')->first();

            if (is_bread_translatable($dataType)) {
                $dataType->deleteAttributeTranslations($dataType->getTranslatableAttributes());
            }

            if ($dataType) {
                DataType::where('name', '%s')->delete();
            }
TXT;

    /** @var string Menu Insert Statement */
    const MENU_INSERT_STATEMENT = <<<'TXT'
$menu = Menu::where('name', config('voyager.bread.default_menu'))->firstOrFail();

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title' => '%s',
                'url' => '',
                'route' => 'voyager.%s.index',
            ]);

            $order = Voyager::model('MenuItem')->highestOrderMenuItem();

            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target' => '_self',
                    'icon_class' => '%s',
                    'color' => null,
                    'parent_id' => null,
                    'order' => $order,
                ])->save();
            }
TXT;

    /** @var string Menu Delete Statement */
    const MENU_DELETE_STATEMENT = <<<'TXT'
$menuItem = MenuItem::where('route', 'voyager.%s.index');

        if ($menuItem->exists()) {
            $menuItem->delete();
        }
TXT;

    const DATATYPE_SLUG_STATEMENT = <<<'TXT'
$dataType = DataType::where('name', '%s')->first();
TXT;

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
        $inString = false;
        $tabCount = 4;

        // replace array() with []
        $lines   = explode("\n", $content);

        for ($i = 1; $i < count($lines); $i++) {
            $lines[$i] = ltrim($lines[$i]);
            // Check for closing bracket
            if (strpos($lines[$i], ')') !== false) {
                $tabCount--;
            }

            // Insert tab count
            if ($inString === false) {
                for ($j = 0; $j < $tabCount; $j++) {
                    $lines[$i] = substr_replace($lines[$i], $this->indentCharacter, 0, 0);
                }
            }
            for ($j = 0; $j < strlen($lines[$i]); $j++) {
                // skip character right after an escape \
                if ($lines[$i][$j] == '\\') {
                    $j++;
                } // check string open/end
                elseif ($lines[$i][$j] == '\'') {
                    $inString = ! $inString;
                }
            }
            // check for opening bracket
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
     * @param $dataType
     *
     * @return string
     */
    public function getDeleteStatement($dataType): string
    {
        return sprintf(self::DELETE_STATMENT, $dataType->name, $dataType->name);
    }

    public function getDataTypeSlugStatement($dataType) : string
    {
        return sprintf(self::DATATYPE_SLUG_STATEMENT, $dataType->name);
    }

    /**
     * Generate Menu Delete Statements.
     *
     * @param $dataType
     *
     * @return string
     */
    public function generateMenuDeleteStatements($dataType) : string
    {
        return sprintf(self::MENU_DELETE_STATEMENT, $dataType->slug);
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
Voyager::model('Permission')->generateFor('%s');
TXT;

        if (! is_null($type)) {
            $permission = <<<'TXT'
Voyager::model('Permission')->removeFrom('%s');
TXT;
        }

        return sprintf($permission, $dataType->name);
    }

    /**
     * Get Menu Insert Statements.
     *
     * @param $dataType
     *
     * @return string
     */
    public function getMenuInsertStatements($dataType) : string
    {
        return sprintf(
            self::MENU_INSERT_STATEMENT,
            $dataType->display_name_plural,
            $dataType->slug,
            $dataType->icon
        );
    }

    /**
     * Generate Orchestra Seeder Content.
     *
     * @param string $className
     * @param string $content
     *
     * @return mixed|null|string|string[]
     */
    public function generateOrchestraSeederContent($className, $content)
    {
        if (strpos($className, FileGenerator::DELETED_SEEDER_SUFFIX) !== false) {
            $toBeDeletedClassName = strstr(
                $className,
                FileGenerator::DELETED_SEEDER_SUFFIX,
                true
            );
            $breadTypeAddedClass = $toBeDeletedClassName . FileGenerator::TYPE_SEEDER_SUFFIX;

            $breadRowAddedClass = $toBeDeletedClassName . FileGenerator::ROW_SEEDER_SUFFIX;

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
