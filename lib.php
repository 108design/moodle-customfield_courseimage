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
defined('MOODLE_INTERNAL') || die();

/** Serve field images only within the selected field's native visibility/editing permissions. */
function customfield_courseimage_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB;
    require_login();
    $itemid = (int)array_shift($args);
    if ($filearea === 'value') {
        $record = $DB->get_record('customfield_data', ['id' => $itemid]);
        if (!$record) {
            send_file_not_found();
        }
        $field = \core_customfield\field_controller::create($record->fieldid);
        $handler = $field->get_handler();
        if ($handler->get_component() !== 'core_course' || $handler->get_area() !== 'course') {
            send_file_not_found();
        }
        if ($field->get('type') !== 'courseimage' || $record->contextid != $context->id ||
                !\customfield_courseimage\access::can_view($field, (int)$record->instanceid)) {
            send_file_not_found();
        }
        if ($context instanceof \context_course) {
            require_login($context->instanceid);
        }
    } else if ($filearea === 'defaultvalue') {
        $field = \core_customfield\field_controller::create($itemid);
        $handler = $field->get_handler();
        if ($field->get('type') !== 'courseimage' || $handler->get_configuration_context()->id != $context->id) {
            send_file_not_found();
        }
        if (!$handler->can_configure()) {
            $courseid = optional_param('courseid', 0, PARAM_INT);
            // A default URL is not a bypass for a hidden field or a field from a different handler.
            if (!$courseid || $handler->get_component() !== 'core_course' || $handler->get_area() !== 'course' ||
                    !\customfield_courseimage\access::can_view($field, $courseid)) {
                send_file_not_found();
            }
            require_login($courseid);
        }
    } else {
        send_file_not_found();
    }
    $filename = array_pop($args);
    $filepath = '/' . ($args ? implode('/', $args) . '/' : '');
    $file = get_file_storage()->get_file($context->id, 'customfield_courseimage', $filearea, $itemid, $filepath, $filename);
    if (!$file || !\customfield_courseimage\image_file::valid($file)) {
        send_file_not_found();
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
}
