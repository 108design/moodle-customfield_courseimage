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
$string['pluginname'] = 'Bild';
$string['specificsettings'] = 'Bildeinstellungen';
$string['defaultimage'] = 'Standardbild';
$string['defaultimage_help'] = 'Optionales Bild für Kurse ohne gespeicherten Feldwert. Ein bewusst geleerter Kurswert unterdrückt das Standardbild.';
$string['image'] = 'Kursbild';
$string['image_help'] = 'Lade ein PNG-, JPEG- oder GIF-Bild hoch (höchstens 10 MiB und 16 Millionen Pixel). Das Entfernen des Bildes speichert einen leeren Wert.';
$string['invalidimage'] = 'Lade genau ein gültiges lokales PNG-, JPEG- oder GIF-Bild mit höchstens 10 MiB und 16 Millionen Pixeln hoch.';
$string['privacy:metadata'] = 'Die Bildwerte werden vom Moodle-Subsystem für benutzerdefinierte Felder verwaltet und beschrieben. Dieses Plugin ergänzt Export und Löschung der zugehörigen Dateien.';
