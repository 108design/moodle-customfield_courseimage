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

/** Native upload/default lifecycle, required fields, privacy and course backup integration. */
final class data_controller_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    private function field(array $config = []): \core_customfield\field_controller {
        $gen = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $category = $gen->create_category();
        return $gen->create_field(['categoryid' => $category->get('id'), 'type' => 'courseimage', 'configdata' => $config]);
    }

    private function png(int $red = 0): string {
        $image = imagecreatetruecolor(120, 40);
        imagefill($image, 0, 0, imagecolorallocate($image, $red, 30, 90));
        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        imagedestroy($image);
        return $data;
    }

    private function draft(string $content, string $name = 'signature.png', int $draftid = 0): int {
        global $USER;
        $draftid = $draftid ?: file_get_unused_draft_itemid();
        get_file_storage()->create_file_from_string(['contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid,
            'filepath' => '/', 'filename' => $name], $content);
        return $draftid;
    }

    public function test_default_upload_course_copy_clear_and_field_deletion(): void {
        $field = $this->field();
        $config = $field->get('configdata');
        $config['defaultimage'] = $this->draft($this->png());
        $field->set('configdata', json_encode($config));
        $field->save();
        $this->assertSame('signature.png', $field->get_configdata_property('defaultfilename'));
        $course = $this->getDataGenerator()->create_course();
        $data = \core_customfield\data_controller::create(0, (object)['instanceid' => $course->id], $field);
        $this->assertNotNull($data->get_image_file());
        $instance = (object)['id' => $course->id];
        $data->instance_form_before_set_data($instance);
        $this->assertCount(0, $data->instance_form_validation((array)$instance, []));
        $data->instance_form_save($instance);
        $this->assertSame('value', $data->get_image_file()->get_filearea());
        $this->assertStringContainsString('signature.png', $data->export_value());
        $contextid = $data->get_context()->id;
        $dataid = $data->get('id');
        $data->instance_form_save((object)[$data->get_form_element_name() => file_get_unused_draft_itemid()]);
        $this->assertNull($data->get_image_file());
        $this->assertSame('', $data->get_value());
        $this->assertNull($data->export_value());
        $data->instance_form_save((object)[$data->get_form_element_name() => $this->draft($this->png())]);
        $this->assertNotNull($data->get_image_file());
        $fieldid = $field->get('id');
        $field->delete();
        $this->assertEmpty(get_file_storage()->get_area_files($contextid, 'customfield_courseimage', 'value', $dataid));
        $this->assertEmpty(get_file_storage()->get_area_files(\context_system::instance()->id,
            'customfield_courseimage', 'defaultvalue', $fieldid));
    }

    public function test_required_invalid_content_and_multiple_uploads(): void {
        $field = $this->field(['required' => 1]);
        $course = $this->getDataGenerator()->create_course();
        $data = \core_customfield\data_controller::create(0, (object)['instanceid' => $course->id], $field);
        $name = $data->get_form_element_name();
        $this->assertArrayHasKey($name, $data->instance_form_validation([$name => file_get_unused_draft_itemid()], []));
        $invalid = $this->draft('this is not PNG');
        $this->assertFalse(image_file::valid_draft($invalid));
        $this->assertArrayHasKey($name, $data->instance_form_validation([$name => $invalid], []));
        $multiple = $this->draft($this->png());
        $this->draft($this->png(), 'second.png', $multiple);
        $this->assertFalse(image_file::valid_draft($multiple));
        $this->assertArrayHasKey('configdata[defaultimage]',
            $field->config_form_validation(['configdata' => ['defaultimage' => $invalid]], []));
        $this->expectException(\moodle_exception::class);
        $data->instance_form_save((object)[$name => $invalid]);
    }

    public function test_privacy_callbacks_remove_only_selected_files(): void {
        $field = $this->field();
        $course = $this->getDataGenerator()->create_course();
        $gen = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $data = $gen->add_instance_data($field, $course->id, $this->draft($this->png()));
        $this->assertNotNull($data->get_image_file());
        \customfield_courseimage\privacy\provider::before_delete_data('= :dataid',
            ['dataid' => $data->get('id')], [$data->get_context()->id]);
        $this->assertNull($data->get_image_file());
    }

    public function test_hidden_field_is_not_exposed_by_unlocked_can_edit(): void {
        $field = $this->field(['visibility' => 0, 'locked' => 0]);
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($student);
        $this->assertTrue($field->get_handler()->can_edit($field, $course->id));
        $this->assertFalse(access::can_view($field, $course->id));
        $this->setUser($teacher);
        $this->assertTrue(access::can_view($field, $course->id));
        $this->assertFalse(access::can_view($field, 0));
    }

    public function test_backup_restore_with_two_images_before_textarea(): void {
        $this->backup_roundtrip(['courseimage', 'courseimage', 'textarea']);
    }

    public function test_backup_restore_with_textarea_before_two_images(): void {
        $this->backup_roundtrip(['textarea', 'courseimage', 'courseimage']);
    }

    private function backup_roundtrip(array $types): void {
        global $CFG;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $course = $this->getDataGenerator()->create_course();
        $gen = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $category = $gen->create_category();
        $expected = [];
        foreach ($types as $index => $type) {
            $field = $gen->create_field(['categoryid' => $category->get('id'), 'type' => $type]);
            $bytes = $this->png(40 + $index * 70);
            if ($type === 'courseimage') {
                $data = $gen->add_instance_data($field, $course->id, $this->draft($bytes));
                $file = $data->get_image_file();
            } else {
                $data = \core_customfield\data_controller::create(0, (object)['instanceid' => $course->id], $field);
                $data->set('contextid', \context_course::instance($course->id)->id);
                $data->set('value', '<img src="@@PLUGINFILE@@/signature.png">');
                $data->set('valueformat', FORMAT_HTML);
                $data->save();
                $file = get_file_storage()->create_file_from_string(['contextid' => $data->get_context()->id,
                    'component' => 'customfield_textarea', 'filearea' => 'value', 'itemid' => $data->get('id'),
                    'filepath' => '/', 'filename' => 'signature.png'], $bytes);
            }
            $expected[$field->get('id')] = [$type, $file->get_contenthash()];
        }
        $backup = new \backup_controller(\backup::TYPE_1COURSE, $course->id, \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, 2);
        $backup->execute_plan();
        $backupid = $backup->get_backupid();
        $backup->destroy();
        $files = get_file_storage()->get_area_files(\context_course::instance($course->id)->id,
            'backup', 'course', false, 'id', false);
        $backupfile = reset($files);
        get_file_packer('application/vnd.moodle.backup')->extract_to_pathname($backupfile,
            $CFG->tempdir . '/backup/' . $backupid);
        $newcourseid = \restore_dbops::create_new_course('Restored image', 'restored-image', $course->category);
        $restore = new \restore_controller($backupid, $newcourseid, \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL, 2, \backup::TARGET_NEW_COURSE);
        $this->assertTrue($restore->execute_precheck());
        $restore->execute_plan();
        $restore->destroy();
        $handler = \core_course\customfield\course_handler::create();
        $values = $handler->get_instance_data($newcourseid, true);
        $this->assertCount(count($types), $values);
        foreach ($values as $value) {
            [$type, $hash] = $expected[$value->get_field()->get('id')];
            $file = $type === 'courseimage' ? $value->get_image_file() : get_file_storage()->get_file(
                $value->get_context()->id, 'customfield_textarea', 'value', $value->get('id'), '/', 'signature.png');
            $this->assertNotEmpty($file, 'Restored file for ' . $type);
            $this->assertSame($hash, $file->get_contenthash());
        }
    }
}
