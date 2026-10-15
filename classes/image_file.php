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
namespace customfield_courseimage;
defined('MOODLE_INTERNAL') || die();

/** Raster image validation shared by the upload form and File API consumer. */
final class image_file {
    public const MAXBYTES = 10485760;
    public const MAXPIXELS = 16000000;

    /** File manager options for one embedded, locally stored image. */
    public static function options(): array {
        return ['maxfiles' => 1, 'subdirs' => 0, 'maxbytes' => self::MAXBYTES,
            'accepted_types' => ['.png', '.jpg', '.jpeg', '.gif'], 'return_types' => FILE_INTERNAL];
    }

    /** Check actual content, not the filename or supplied MIME type. */
    public static function valid(\stored_file $file): bool {
        if ($file->is_directory() || $file->is_external_file() || $file->get_filesize() > self::MAXBYTES) {
            return false;
        }
        $info = @getimagesizefromstring($file->get_content());
        if (!$info || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF], true)
                || $info[0] <= 0 || $info[1] <= 0 || $info[0] * $info[1] > self::MAXPIXELS) {
            return false;
        }
        $image = @imagecreatefromstring($file->get_content());
        if (!$image) {
            return false;
        }
        imagedestroy($image);
        return true;
    }

    /** Validate an incoming draft area, including uploads via a forged form submission. */
    public static function valid_draft(int $draftid): bool {
        global $USER;
        if (!$draftid) {
            return true;
        }
        $files = get_file_storage()->get_area_files(\context_user::instance($USER->id)->id,
            'user', 'draft', $draftid, 'id', false);
        return count($files) <= 1 && (!$files || self::valid(reset($files)));
    }
}
