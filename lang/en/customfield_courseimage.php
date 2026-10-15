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
$string['pluginname'] = 'Image';
$string['specificsettings'] = 'Image settings';
$string['defaultimage'] = 'Default image';
$string['defaultimage_help'] = 'Optional image for courses without a saved field value. Clearing a saved course value suppresses the default.';
$string['image'] = 'Course image';
$string['image_help'] = 'Upload one PNG, JPEG or GIF image (maximum 10 MiB and 16 million pixels). Removing the image saves an empty value.';
$string['invalidimage'] = 'Upload one valid local PNG, JPEG or GIF image, no larger than 10 MiB or 16 million pixels.';
$string['privacy:metadata'] = 'Image values are owned and described by the Moodle custom fields subsystem; this plugin includes the associated files in its export and deletion callbacks.';
