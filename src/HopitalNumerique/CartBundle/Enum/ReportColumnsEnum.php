<?php

namespace HopitalNumerique\CartBundle\Enum;

use HopitalNumerique\CartBundle\Entity\Report;

abstract class ReportColumnsEnum
{
    const RESUME_COLUMN = 'resume';
    const SYNTHESIS_COLUMN = 'synthesis';
    const SUMMARY_COLUMN = 'summary';
    const CONTENT_COLUMN = 'content';
    const COMMENT_COLUMN = 'comment';
    const REFERENCE_COLUMN = 'reference';

    /**
     * @return array
     */
    static function getColumns()
    {
        return [
            self::RESUME_COLUMN => self::RESUME_COLUMN,
            self::SYNTHESIS_COLUMN => self::SYNTHESIS_COLUMN,
            self::SUMMARY_COLUMN => self::SUMMARY_COLUMN,
            self::CONTENT_COLUMN => self::CONTENT_COLUMN,
            self::COMMENT_COLUMN => self::COMMENT_COLUMN,
            self::REFERENCE_COLUMN => self::REFERENCE_COLUMN,
        ];
    }

    /**
     * Returns default columns for new reports
     *
     * @return array
     */
    static function getDefaultColumns()
    {
        return [
            self::RESUME_COLUMN,
        ];
    }

    /**
     * @param Report $report
     *
     * @return array
     */
    static function getReportColumns(Report $report)
    {
        $columns = [];

        foreach (self::getColumns() as $column) {
            $columns[$column] = !is_null($report) && in_array($column, $report->getColumns());
        }

        return $columns;
    }

    /**
     * Returns default columns for new reports
     *
     * @return array
     */
    static function getReportDefaultColumns()
    {
        return array_map(function ($column) {
            return in_array($column, self::getDefaultColumns());
        }, self::getColumns());
    }
}
