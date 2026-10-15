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

/** Configuration of a single-image custom field. */
class field_controller extends \core_customfield\field_controller {
    public const TYPE = 'courseimage';

    /** Add the optional default image. */
    public function config_form_definition(\MoodleQuickForm $mform) {
        // Filename uniqueness would not establish image-content uniqueness.
        if ($mform->elementExists('configdata[uniquevalues]')) {
            $mform->removeElement('configdata[uniquevalues]');
        }
        $mform->addElement('header', 'courseimage_settings', get_string('specificsettings', 'customfield_courseimage'));
        $mform->addElement('filemanager', 'configdata[defaultimage]', get_string('defaultimage', 'customfield_courseimage'),
            null, image_file::options());
        $mform->addHelpButton('configdata[defaultimage]', 'defaultimage', 'customfield_courseimage');
    }

    /** Restore the default draft area before the configuration form is displayed. */
    public function prepare_for_config_form(\stdClass $formdata) {
        $draftid = file_get_submitted_draft_itemid('courseimage_default');
        file_prepare_draft_area($draftid, $this->get_handler()->get_configuration_context()->id,
            'customfield_courseimage', 'defaultvalue', $this->get('id') ?: 0, image_file::options());
        $formdata->configdata['defaultimage'] = $draftid;
    }

    /** Reject invalid image contents before saving. */
    public function config_form_validation(array $data, $files = []): array {
        return image_file::valid_draft((int)($data['configdata']['defaultimage'] ?? 0)) ? [] :
            ['configdata[defaultimage]' => get_string('invalidimage', 'customfield_courseimage')];
    }

    /** Keep the file in an area keyed to the field definition. */
    public function save() {
        $config = $this->get('configdata');
        $config['uniquevalues'] = 0;
        $this->set('configdata', json_encode($config));
        if (!array_key_exists('defaultimage', $config)) {
            return parent::save();
        }
        $draftid = (int)$config['defaultimage'];
        if (!image_file::valid_draft($draftid)) {
            throw new \moodle_exception('invalidimage', 'customfield_courseimage');
        }
        unset($config['defaultimage']);
        $this->set('configdata', json_encode($config));
        parent::save();
        file_save_draft_area_files($draftid, $this->get_handler()->get_configuration_context()->id,
            'customfield_courseimage', 'defaultvalue', $this->get('id'), image_file::options());
        $files = get_file_storage()->get_area_files($this->get_handler()->get_configuration_context()->id,
            'customfield_courseimage', 'defaultvalue', $this->get('id'), 'id', false);
        $config['defaultfilename'] = $files ? reset($files)->get_filename() : '';
        $this->set('configdata', json_encode($config));
        parent::save();
    }

    /** Remove definition files and every value file before native record deletion. */
    public function delete(): bool {
        global $DB;
        foreach ($DB->get_records('customfield_data', ['fieldid' => $this->get('id')]) as $record) {
            get_file_storage()->delete_area_files($record->contextid, 'customfield_courseimage', 'value', $record->id);
        }
        get_file_storage()->delete_area_files($this->get_handler()->get_configuration_context()->id,
            'customfield_courseimage', 'defaultvalue', $this->get('id'));
        return parent::delete();
    }
}
