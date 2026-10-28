<?php
/**
 * File documentation for classes/access.php.
 *
 * @package   customfield_courseimage
 * @copyright 2026 Andreas Giesen
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
namespace customfield_courseimage;
defined('MOODLE_INTERNAL') || die();

/** Field visibility and course editing authority for protected image requests. */
final class access {
    /** The native handler's can_edit checks the field lock, not course-update authority. */
    public static function can_view(\core_customfield\field_controller $field, int $courseid): bool {
        $handler = $field->get_handler();
        if ($field->get('type') !== 'courseimage' || $handler->get_component() !== 'core_course' ||
                $handler->get_area() !== 'course') {
            return false;
        }
        $context = $courseid > SITEID ? \context_course::instance($courseid, IGNORE_MISSING) : false;
        return $context && ($handler->can_view($field, $courseid) ||
            (has_capability('moodle/course:update', $context) && $handler->can_edit($field, $courseid)));
    }
}
