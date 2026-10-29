# Course image

Add a single-image upload to Moodle's custom course fields. Use it for signatures, course logos or other course-specific images.
<details>
<summary>Screenshots</summary>

<table><tr><td><a href="https://raw.githubusercontent.com/108design/moodle-customfield_courseimage/main/docs/screenshots/course-image-upload.png"><img src="https://raw.githubusercontent.com/108design/moodle-customfield_courseimage/main/docs/screenshots/course-image-upload.png" alt="Image upload saved in native course settings" width="420"></a><br>Image upload saved in native course settings</td><td><a href="https://raw.githubusercontent.com/108design/moodle-customfield_courseimage/main/docs/screenshots/course-certificate.png"><img src="https://raw.githubusercontent.com/108design/moodle-customfield_courseimage/main/docs/screenshots/course-certificate.png" alt="Course-specific image in a generated certificate" width="420"></a><br>Course-specific image in a generated certificate</td></tr></table>

</details>



## Certificate Multitool set

Use the text pair for course-managed wording and the image pair for course-specific pictures in a shared certificate template. Flexible Date supplies the date formatting. Choose the parts your workflow needs.

| Plugin | Role and companion |
| --- | --- |
| [Certificate Multitool – Custom Field](https://github.com/108design/moodle-customfield_multiline) | Stores course text, text mode and alignment. Pair it with Cert. Element for certificate output. |
| [Certificate Multitool – Cert. Element](https://github.com/108design/moodle-certificateelement_coursefieldtext) | Renders that course text and supplies the course HTML preview. Requires Custom Field and Certificate manager. |
| [Course image](https://github.com/108design/moodle-customfield_courseimage) | Provides a single-image course field. Pair it with Course field image for certificate output. |
| [Course field image](https://github.com/108design/moodle-certificateelement_coursefieldimg) | Places a course image on the certificate and preserves the issued image copy. Requires Certificate manager; it accepts Course image or a native textarea field with an internal image. |
| [Flexible Date](https://github.com/108design/moodle-certificateelement_flexdate) | Formats certificate issue and expiry dates. Requires Certificate manager. |

**Text pair:** install Custom Field and Cert. Element together for the course-text certificate workflow. **Image pair:** install Course image and Course field image together for the single-image upload workflow. The image element can also use a native textarea image without Course image. Course certificate is needed for course-based issuance and the text pair’s course HTML preview.

### Moodle 5.3 qualification

Moodle 5.3 integration is provisional: it has been tested with Certificate manager (tool_certificate) 5.0.10/5.0.11 outside those versions’ declared Moodle 5.0–5.2 support range. Check the compatibility of Certificate manager and, when used, Course certificate before deploying on Moodle 5.3. The course custom fields themselves do not require Certificate manager.


## Requirements and installation

Requires Moodle 4.5 to 5.3. Install in customfield/field/courseimage and complete the Moodle upgrade. Add a custom course field of type **Image** under Site administration > Courses > Course custom fields. Certificate manager is not required.

This stable release has native Moodle 4.5 and provisional Moodle 5.3 integration evidence. Validate your course-editing and backup/restore workflow on a staging site before production use.

## Use

Upload one PNG, JPEG or GIF image in the course settings (maximum 10 MiB and 16 million pixels). Transparent PNG files are suitable for signatures. External repository aliases and SVG files are not supported. Moodle's native field visibility, locking and course permissions apply.

You may configure a default image. It is used while a course has no saved value. Opening a new course value prepares a copy in the upload area; saving keeps that course's own selection. Removing an image and saving stores an intentionally empty value and does not restore the default. Course backup and restore include the uploaded image.

The separate **Image from course field** certificate element can use this field. It retains a copy at issuance, so a later upload affects fresh certificates. A hidden course field can still be selected for a certificate; only use images intended for its recipients.

## License

Copyright 2026 Andreas Giesen. See [GNU GPL v3 or later](LICENSE.md). Moodle retains its own licence.

This release is available free of charge under the GNU GPL v3 or later.

Available free of charge under the terms of the applicable license.
