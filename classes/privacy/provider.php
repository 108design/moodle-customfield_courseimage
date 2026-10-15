<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
// SPDX-License-Identifier: GPL-3.0-or-later
//
// This plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this plugin. If not, see <https://www.gnu.org/licenses/>.
/**
 * customfield_courseimage.
 * @package customfield_courseimage
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace customfield_courseimage\privacy;
defined('MOODLE_INTERNAL') || die();

/** Native custom-field privacy integration; ownership is handled by core_customfield. */
class provider implements \core_privacy\local\metadata\null_provider,
        \core_customfield\privacy\customfield_provider {
    /** Data is described through the custom-field subsystem. */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }

    /** Include attached images with the native value export. */
    public static function export_customfield_data(\core_customfield\data_controller $data, \stdClass $exportdata,
            array $subcontext) {
        \core_privacy\local\request\writer::with_context($data->get_context())->export_data($subcontext, $exportdata)
            ->export_area_files($subcontext, 'customfield_courseimage', 'value', $data->get('id'));
    }

    /** Remove course-value images selected by the native privacy request. */
    public static function before_delete_data(string $dataidstest, array $params, array $contextids) {
        foreach ($contextids as $id) {
            get_file_storage()->delete_area_files_select($id, 'customfield_courseimage', 'value', $dataidstest, $params);
        }
    }

    /** Remove default files if their field definitions are deleted. */
    public static function before_delete_fields(string $fieldidstest, array $params, array $contextids) {
        foreach ($contextids as $id) {
            get_file_storage()->delete_area_files_select($id, 'customfield_courseimage', 'defaultvalue', $fieldidstest, $params);
        }
    }
}
