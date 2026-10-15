# Course image

Add a single-image upload to Moodle's custom course fields. Use it for signatures, course logos or other course-specific images.

## Requirements and installation

Requires Moodle 4.5 to 5.3. Install in customfield/field/courseimage and complete the Moodle upgrade. Add a custom course field of type **Image** under Site administration > Courses > Course custom fields. Certificate manager is not required.

This is a beta release, verified with native Moodle 4.5 and 5.3 workflows. Validate your course-editing and backup/restore workflow on a staging site before production use.

## Use

Upload one PNG, JPEG or GIF image in the course settings (maximum 10 MiB and 16 million pixels). Transparent PNG files are suitable for signatures. External repository aliases and SVG files are not supported. Moodle's native field visibility, locking and course permissions apply.

You may configure a default image. It is used while a course has no saved value. Opening a new course value prepares a copy in the upload area; saving keeps that course's own selection. Removing an image and saving stores an intentionally empty value and does not restore the default. Course backup and restore include the uploaded image.

The separate **Image from course field** certificate element can use this field. It retains a copy at issuance, so a later upload affects fresh certificates. A hidden course field can still be selected for a certificate; only use images intended for its recipients.

## License

Copyright 2026 Andreas Giesen. See [GNU GPL v3 or later](LICENSE.md). Moodle retains its own licence.

This release is available free of charge under the GNU GPL v3 or later.
