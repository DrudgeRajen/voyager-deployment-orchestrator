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
    public function repackContentData($data) : array
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
     * @param string $className
     * @param string $stub
     * @param DataType $dataType
     * @param string $suffix
     *
     * @return mixed|string
     */
    public function populateContentToStubFile(
        string $className,
        string $stub,
        DataType $dataType,
        string $suffix
    ) {
        $stub = str_replace('{{class}}', $className, $stub);

        $inserts = '';

        switch ($suffix) {
            case FilesGenerator::TYPE_SEEDER_SUFFIX:
                $tableName = $dataType->getTable();

                $stub = str_replace('{{delete_statements}}',
                    $this->contentGenerator->getDeleteStatement($dataType),
                    $stub
                );
                if ($dataType->generate_permissions) {
                    $stub = str_replace('{{permission_insert_statements}}',
                        $this->contentGenerator->getPermissionStatement($dataType),
                        $stub
                    );
                } else {
                    $stub = str_replace('{{permission_insert_statements}}',
                        '',
                        $stub
                    );
                }
                $stub = str_replace('{{menu_insert_statements}}',
                    $this->contentGenerator->getMenuInsertStatements($dataType),
                    $stub
                );
                $dataTypeArray = $dataType->toArray();
                unset($dataTypeArray['id']);

                if (isset($dataTypeArray['translations'])) {
                    $translations = $this->repackContentData($dataTypeArray['translations']);
                    $stub = str_replace('{{datatype_slug_statements}}',
                        $this->contentGenerator->getDataTypeSlugStatement($dataType),
                        $stub
                    );

                    $translationInsertStatement = '';

                    $translationInsertStatement .= sprintf(
                        "\DB::table('%s')->insert(%s);",
                        'translations',
                        $this->contentGenerator->formatContent($translations)
                    );

                    $stub = str_replace('{{translation_insert_statements}}', $translationInsertStatement, $stub);
                    unset($dataTypeArray['translations']);
                } else {
                    $stub = str_replace('{{translation_insert_statements}}',
                        '',
                        $stub
                    );
                    $stub = str_replace('{{datatype_slug_statements}}',
                        '',
                        $stub
                    );
                }

                $inserts .= sprintf(
                    "\DB::table('%s')->insert(%s);",
                    $tableName,
                    $this->contentGenerator->formatContent($dataTypeArray)
                );

                $stub = str_replace('{{insert_statements}}', $inserts, $stub);

                break;
            case FilesGenerator::ROW_SEEDER_SUFFIX:
                $rows = $dataType->rows;

                $dataArray = $this->repackContentData($rows->toArray());

                $tableName = $rows->last()->getTable();

                $stub = str_replace('{{datatype_slug_statement}}',
                    $this->contentGenerator->getDataTypeSlugStatement($dataType),
                    $stub
                );

                $inserts .= sprintf(
                    "\DB::table('%s')->insert(%s);",
                    $tableName,
                    $this->contentGenerator->formatContent($dataArray)
                );

                $stub = str_replace('{{insert_statements}}', $inserts, $stub);
                break;

            case FilesGenerator::DELETED_SEEDER_SUFFIX:
                $stub = str_replace('{{delete_statements}}',
                    $this->contentGenerator->getDeleteStatement($dataType),
                    $stub
                );

                if ($dataType->generate_permissions) {
                    $stub = str_replace('{{permission_delete_statements}}',
                        $this->contentGenerator->getPermissionStatement($dataType, 'delete'),
                        $stub
                    );
                } else {
                    $stub = str_replace('{{permission_delete_statements}}',
                        '',
                        $stub
                    );
                }

                $stub = str_replace(
                    '{{menu_delete_statements}}',
                    $this->contentGenerator->generateMenuDeleteStatements($dataType),
                    $stub
                );
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
}
