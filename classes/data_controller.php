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

/** Course data and file lifecycle for the image field. */
class data_controller extends \core_customfield\data_controller {
    /** Store the filename, including an intentional empty value. */
    public function datafield(): string {
        return 'value';
    }

    /** Default is used only when the course has no data record. */
    public function get_default_value() {
        return (string)$this->get_field()->get_configdata_property('defaultfilename');
    }

    /** Single-file upload on the native course form. */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        $name = $this->get_form_element_name();
        $mform->addElement('filemanager', $name, $this->get_field()->get_formatted_name(), null, image_file::options());
        $mform->addHelpButton($name, 'image', 'customfield_courseimage');
        if ($this->get_field()->get_configdata_property('required')) {
            $mform->addRule($name, null, 'required', null, 'client');
        }
    }

    /** Prepare either stored course files or a copy of the default for a new value. */
    public function instance_form_before_set_data(\stdClass $instance) {
        $draftid = file_get_submitted_draft_itemid($this->get_form_element_name());
        $field = $this->get_field();
        $contextid = $this->get('id') ? $this->get_context()->id : $field->get_handler()->get_configuration_context()->id;
        file_prepare_draft_area($draftid, $contextid, 'customfield_courseimage',
            $this->get('id') ? 'value' : 'defaultvalue', $this->get('id') ?: $field->get('id'), image_file::options());
        $instance->{$this->get_form_element_name()} = $draftid;
    }

    /** Validate content and the native required-field contract. */
    public function instance_form_validation(array $data, array $files): array {
        $name = $this->get_form_element_name();
        if (!array_key_exists($name, $data)) {
            return [];
        }
        $draftid = (int)$data[$name];
        if (!image_file::valid_draft($draftid)) {
            return [$name => get_string('invalidimage', 'customfield_courseimage')];
        }
        $info = file_get_draft_area_info($draftid);
        if ($this->get_field()->get_configdata_property('required') && !$info['filecount']) {
            return [$name => get_string('required')];
        }
        return [];
    }

    /** Save a native draft into the course context; omitted fields retain their current value. */
    public function instance_form_save(\stdClass $data) {
        $name = $this->get_form_element_name();
        if (!property_exists($data, $name)) {
            return;
        }
        $errors = $this->instance_form_validation((array)$data, []);
        if ($errors) {
            throw new \moodle_exception('invalidimage', 'customfield_courseimage');
        }
        if (!$this->get('id')) {
            $this->set('contextid', $this->get_context()->id);
            $this->set('value', '');
            $this->save();
        }
        file_save_draft_area_files((int)$data->$name, $this->get_context()->id, 'customfield_courseimage',
            'value', $this->get('id'), image_file::options());
        $files = get_file_storage()->get_area_files($this->get_context()->id, 'customfield_courseimage',
            'value', $this->get('id'), 'id', false);
        $this->set('value', $files ? reset($files)->get_filename() : '');
        $this->set('valueformat', FORMAT_PLAIN);
        $this->save();
    }

    /** Direct File API accessor for authorised server-side consumers. */
    public function get_image_file(): ?\stored_file {
        $name = $this->get_value();
        if (!$name) {
            return null;
        }
        $field = $this->get_field();
        $file = get_file_storage()->get_file($this->get('id') ? $this->get_context()->id :
            $field->get_handler()->get_configuration_context()->id, 'customfield_courseimage',
            $this->get('id') ? 'value' : 'defaultvalue', $this->get('id') ?: $field->get('id'), '/', $name);
        return $file && image_file::valid($file) ? $file : null;
    }

    /** Render a protected preview through Moodle's file serving callback. */
    public function export_value() {
        $file = $this->get_image_file();
        if (!$file) {
            return null;
        }
        $url = \moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        $url->param('courseid', $this->get('instanceid'));
        return \html_writer::empty_tag('img', ['src' => $url, 'alt' => $this->get_field()->get_formatted_name(),
            'class' => 'customfield-courseimage-preview', 'style' => 'max-width:100%;max-height:120px;object-fit:contain']);
    }

    /** Remove course-owned files. */
    public function delete() {
        get_file_storage()->delete_area_files($this->get_context()->id, 'customfield_courseimage', 'value', $this->get('id'));
        return parent::delete();
    }

    /** Include files in course backup. */
    public function backup_define_structure(\backup_nested_element $element): void {
        $annotations = $element->get_file_annotations();
        if (!isset($annotations['customfield_courseimage']['value'])) {
            $element->annotate_files('customfield_courseimage', 'value', 'id');
        }
    }

    /** Map the custom-field record before restoring its files. */
    public function restore_define_structure(\restore_structure_step $step, int $newid, int $oldid): void {
        if ((int)$this->get('id') !== $newid) {
            return;
        }
        // Native handlers call every field controller for each restored value.
        // Keep our file mapping independent of textarea's shared record mapping.
        $mapping = 'customfield_courseimage_data';
        if (!$step->get_mappingid($mapping, $oldid)) {
            $step->set_mapping($mapping, $oldid, $newid, true);
            $step->add_related_files('customfield_courseimage', 'value', $mapping, null, $oldid);
        }
    }
}
