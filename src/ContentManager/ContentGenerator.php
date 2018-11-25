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
        $tabCount = 3;
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
        $delete = '';
        $delete .= "\$dataType = DataType::find('" . $dataType->id . "');";
        $delete .= $this->addNewLines($delete, 2);
        $delete .= $this->addIndent($delete, 2);
        $delete .= 'if (is_bread_translatable($dataType)) {';
        $delete .= $this->addNewLines($delete);
        $delete .= $this->addIndent($delete, 3);
        $delete .= '$dataType->deleteAttributeTranslations($dataType->getTranslatableAttributes());';
        $delete .= $this->addNewLines($delete);
        $delete .= $this->addIndent($delete, 3);
        $delete .= '}';
        $delete .= $this->addNewLines($delete, 2);
        $delete .= $this->addIndent($delete, 2);
        $delete .= 'if ($dataType) {';
        $delete .= $this->addNewLines($delete);
        $delete .= $this->addIndent($delete, 3);
        $delete .= '$dataType->destroy(' . $dataType->id . ');';
        $delete .= $this->addNewLines($delete);
        $delete .= $this->addIndent($delete, 2);
        $delete .= '}';

        return $delete;
    }

    /**
     * Generate Menu Delete Statements.
     *
     * @param $dataArray
     * @return string
     */
    public function generateMenuDeleteStatements($dataType) : string
    {
        $menuDelete = '';
        $menuDelete .= "\$menuItem = MenuItem::where('route', 'voyager." . $dataType->slug . ".index');";
        $menuDelete .= $this->addNewLines($menuDelete, 2);
        $menuDelete .= $this->addIndent($menuDelete, 2);
        $menuDelete .= 'if ($menuItem->exists()) {';
        $menuDelete .= $this->addNewLines($menuDelete);
        $menuDelete .= $this->addIndent($menuDelete, 2);
        $menuDelete .= '$menuItem->delete();';
        $menuDelete .= $this->addNewLines($menuDelete);
        $menuDelete .= $this->addIndent($menuDelete, 2);
        $menuDelete .= '}';

        return $menuDelete;
    }

    /**
     * Get Permission Statements.
     *
     * @param $dataArray
     * @param null $type
     *
     * @return string
     */
    public function getPermissionStatement($dataType, $type = null) : string
    {
        $permission = "Voyager::model('Permission')->generateFor('" . $dataType->name . "');";

        if (! is_null($type)) {
            $permission = "Voyager::model('Permission')->removeFrom('" . $dataType->name . "');";
        }

        return $permission;
    }

    /**
     * Get Menu Insert Statements.
     *
     * @param $dataArray
     * @return string
     */
    public function getMenuInsertStatements($dataType) : string
    {
        $menu = '';
        $menu .= "\$menu = Menu::where('name', config('voyager.bread.default_menu'))->firstOrFail();";
        $this->addNewLines($menu, 2);
        $this->addIndent($menu, 2);
        $menu .= '$menuItem = MenuItem::firstOrNew([';
        $this->addNewLines($menu);
        $this->addIndent($menu, 3);
        $menu .= "'menu_id' => \$menu->id,";
        $this->addNewLines($menu);
        $this->addIndent($menu, 3);
        $menu .= "'title'   => '" . $dataType->display_name_plural . "',";
        $this->addNewLines($menu);
        $this->addIndent($menu, 3);
        $menu .= "'url'     => '',";
        $this->addNewLines($menu);
        $this->addIndent($menu, 3);
        $menu .= "'route'   => 'voyager." . $dataType->slug . ".index',";
        $this->addNewLines($menu);
        $this->addIndent($menu, 2);
        $menu .= ']);';
        $this->addNewLines($menu, 2);
        $this->addIndent($menu, 2);
        $menu .= "\$order = Voyager::model('MenuItem')->highestOrderMenuItem();";
        $this->addNewLines($menu, 2);
        $this->addIndent($menu, 2);
        $menu .= 'if (!$menuItem->exists) {';
        $this->addNewLines($menu);
        $this->addIndent($menu, 3);
        $menu .= '$menuItem->fill([';
        $this->addNewLines($menu);
        $this->addIndent($menu, 4);
        $menu .= "'target'     => '_self',";
        $this->addNewLines($menu);
        $this->addIndent($menu, 4);
        $menu .= "'icon_class' => '" . $dataType->icon . "',";
        $this->addNewLines($menu);
        $this->addIndent($menu, 4);
        $menu .= "'color'      => null,";
        $this->addNewLines($menu);
        $this->addIndent($menu, 4);
        $menu .= "'parent_id'  => null,";
        $this->addNewLines($menu);
        $this->addIndent($menu, 4);
        $menu .= "'order'      => \$order,";
        $this->addNewLines($menu);
        $this->addIndent($menu, 3);
        $menu .= '])->save();';
        $this->addNewLines($menu);
        $this->addIndent($menu, 2);
        $menu .= '}';

        return $menu;
    }

    /**
     * Adds indentation to the passed content reference.
     *
     * @param string $content
     * @param int $numberOfIndents
     */
    private function addIndent(&$content, $numberOfIndents = 1)
    {
        while ($numberOfIndents > 0) {
            $content .= $this->indentCharacter;
            $numberOfIndents--;
        }
    }

    /**
     * Adds new lines to the passed content variable reference.
     *
     * @param string $content
     * @param int $numberOfLines
     */
    private function addNewLines(&$content, $numberOfLines = 1)
    {
        while ($numberOfLines > 0) {
            $content .= $this->newLineCharacter;
            $numberOfLines--;
        }
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
