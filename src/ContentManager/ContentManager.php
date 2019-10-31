<?php

namespace DrudgeRajen\VoyagerDeploymentOrchestrator\ContentManager;

use TCG\Voyager\Models\DataType;

class ContentManager
{
    /** @var ContentGenerator */
    private $contentGenerator;

    /**
     * ContentManager constructor.
     *
     * @param ContentGenerator $contentGenerator
     */
    public function __construct(ContentGenerator $contentGenerator)
    {
        $this->contentGenerator = $contentGenerator;
    }

    /**
     * Repack Content Data.
     *
     * @param $data
     *
     * @return array
     */
    public function repackContentData($data): array
    {
        $dataArray = [];
        if (! empty($data)) {
            foreach ($data as $row) {
                $rowArray = [];
                foreach ($row as $columnName => $columnValue) {
                    if ($columnName === 'details') {
                        $columnValue = json_encode($columnValue);
                    }
                    $rowArray[$columnName] = $columnValue;

                    if ($columnName === 'id') {
                        unset($rowArray[$columnName]);
                    }

                    if ($columnName === 'data_type_id') {
                        $rowArray[$columnName] = '#dataTypeId';
                    }
                    if ($columnName === 'foreign_key') {
                        $rowArray[$columnName] = '#dataTypeId';
                    }
                }
                $dataArray[] = $rowArray;
            }
        }

        return $dataArray;
    }

    /**
     * Populate Content To Stub File.
     *
     * @param string $stub
     * @param DataType $dataType
     * @param string $suffix
     *
     * @return mixed|string
     */
    public function populateContentToStubFile(
        string $stub,
        DataType $dataType,
        string $suffix
    ) {
        switch ($suffix) {
            case FileGenerator::TYPE_SEEDER_SUFFIX:
                $stub = $this->populateDataTypeSeederContent($stub, $dataType);
                break;
            case FileGenerator::ROW_SEEDER_SUFFIX:
                $stub = $this->populateDataRowSeederContent($stub, $dataType);
                break;
            case FileGenerator::DELETED_SEEDER_SUFFIX:
                $stub = $this->populateBreadDeletedSeederContent($stub, $dataType);
                break;
        }

        return $stub;
    }

    /**
     * Update Deployment Orchestra Seeder Content.
     *
     * @param string $className
     * @param string $content
     *
     * @return mixed|null|string|string[]
     */
    public function updateDeploymentOrchestraSeederContent($className, $content)
    {
        return $this->contentGenerator->generateOrchestraSeederContent($className, $content);
    }

    /**
     * Populate Data Type Seeder Content.
     *
     * @param string $stub
     * @param DataType $dataType
     *
     * @return mixed|string
     */
    private function populateDataTypeSeederContent(string $stub, DataType $dataType)
    {
        $tableName = $dataType->getTable();
        $stub      = $this->populateDeleteStatements($stub, $dataType);
        $stub      = $this->populatePermissionStatements($stub, $dataType);
        $stub      = $this->populateMenuStatements($stub, $dataType);

        [$dataType, $stub] = $this->populateTranslationStatements($stub, $dataType);

        $dataTypeArray = $dataType->toArray();

        // Here, we cannot do $dataType->unsetRelations('translations')
        // because voyager first fires events and then saves translations.
        unset($dataTypeArray['translations']);

        return $this->populateInsertStatements(
            $stub,
            $tableName,
            $dataTypeArray,
            '{{insert_statements}}'
        );
    }

    /**
     * Populate Data Row Seeder Content.
     *
     * @param string $stub
     * @param DataType $dataType
     *
     * @return mixed|string
     */
    private function populateDataRowSeederContent(string $stub, DataType $dataType)
    {
        $rows      = $dataType->rows;
        $dataArray = $this->repackContentData($rows->toArray());
        $tableName = $rows->last()->getTable();

        $stub = str_replace(
            '{{datatype_slug_statement}}',
            $this->contentGenerator->getDataTypeSlugStatement($dataType),
            $stub
        );

        return $this->populateInsertStatements(
            $stub,
            $tableName,
            $dataArray,
            '{{insert_statements}}'
        );
    }

    /**
     * Populate Bread Deleted Seeder Content.
     *
     * @param string $stub
     * @param DataType $dataType
     *
     * @return mixed|string
     */
    private function populateBreadDeletedSeederContent(string $stub, DataType $dataType)
    {
        $stub = $this->populateDeleteStatements($stub, $dataType);

        if ($dataType->generate_permissions) {
            $stub = $this->replaceString(
                '{{permission_delete_statements}}',
                $this->contentGenerator->getPermissionStatement($dataType, 'delete'),
                $stub
            );
        } else {
            $stub = $this->replaceString(
                '{{permission_delete_statements}}',
                '',
                $stub
            );
        }

        return $this->replaceString(
            '{{menu_delete_statements}}',
            $this->contentGenerator->generateMenuDeleteStatements($dataType),
            $stub
        );
    }

    /**
     * Populate Permission Statements.
     *
     * @param string $stub
     * @param DataType $dataType
     *
     * @return mixed|string
     */
    private function populatePermissionStatements(string $stub, DataType $dataType)
    {
        if ($dataType->generate_permissions) {
            $stub = $this->replaceString(
                '{{permission_insert_statements}}',
                $this->contentGenerator->getPermissionStatement($dataType),
                $stub
            );
        } else {
            $stub = $this->replaceString(
                '{{permission_insert_statements}}',
                '',
                $stub
            );
        }

        return $stub;
    }

    /**
     * Populate Delete Statements.
     *
     * @param string $stub
     * @param DataType $dataType
     *
     * @return mixed|string
     */
    private function populateDeleteStatements(string $stub, DataType $dataType)
    {
        return $this->replaceString(
            '{{delete_statements}}',
            $this->contentGenerator->getDeleteStatement($dataType),
            $stub
        );
    }

    /**
     * Populate Menu Statements.
     *
     * @param string $stub
     * @param DataType $dataType
     *
     * @return mixed|string
     */
    private function populateMenuStatements(string $stub, DataType $dataType)
    {
        return $this->replaceString(
            '{{menu_insert_statements}}',
            $this->contentGenerator->getMenuInsertStatements($dataType),
            $stub
        );
    }

    /**
     * Populate Translation Statements.
     *
     * @param string $stub
     * @param DataType $dataType
     *
     * @return array
     */
    private function populateTranslationStatements(string $stub, DataType $dataType)
    {
        if (! count($dataType->translations)) {
            $stub = $this->replaceString(
                '{{translation_insert_statements}}',
                '',
                $stub
            );

            $stub = $this->replaceString(
                '{{datatype_slug_statements}}',
                '',
                $stub
            );

            return [$dataType, $stub];
        }

        $tableName    = $dataType->translations->last()->getTable();
        $translations = $this->repackContentData($dataType->translations->toArray());

        $stub = $this->replaceString(
            '{{datatype_slug_statements}}',
            $this->contentGenerator->getDataTypeSlugStatement($dataType),
            $stub
        );

        $stub = $this->populateInsertStatements(
            $stub,
            $tableName,
            $translations,
            '{{translation_insert_statements}}'
        );

        return [$dataType, $stub];
    }

    /**
     * Populate Insert Statements.
     *
     * @param string $stub
     * @param string $tableName
     * @param array $dataTypeArray
     *
     * @return mixed|string
     */
    private function populateInsertStatements(
        string $stub,
        string $tableName,
        array $dataTypeArray,
        $insertStatementString
    ) {
        $inserts = '';
        $inserts .= sprintf(
            "\DB::table('%s')->insert(%s);",
            $tableName,
            $this->contentGenerator->formatContent($dataTypeArray)
        );

        return $this->replaceString($insertStatementString, $inserts, $stub);
    }

    /**
     * Replace String.
     *
     * @param string $search
     * @param string $replace
     * @param $stub
     *
     * @return mixed
     */
    public function replaceString($search, $replace, $stub)
    {
        return str_replace($search, $replace, $stub);
    }

    public function populateTableContentToSeeder(string $stub, string $tableName, array $data)
    {
        return $this->populateInsertStatements($stub, $tableName, $data, '{{insert_statements}}');
    }
}
