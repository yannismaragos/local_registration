<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Registration form page setup.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration\form;

defined('MOODLE_INTERNAL') || die();

use local_registration\manager;
use moodle_url;
use local_registration\encryptor;

require_once("$CFG->libdir/formslib.php");

/**
 * Class registration.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class registration extends \moodleform {
    /**
     * Maximum length allowed for a form field.
     *
     * @var int
     */
    private const FIELD_MAX_LENGTH = 200;

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $SESSION;

        $id = 0;
        $hash = '';
        $tenantid = 0;
        $manager = new manager();
        $encryptor = new encryptor(manager::ENCRYPTION_KEY);

        // Instantiate the form.
        $mform = $this->_form;

        // Get the record id from the url.
        if ($id = optional_param('id', 0, PARAM_INT)) {
            $record = $manager->get_registration_record($id);

            if (empty($record)) {
                redirect(
                    new moodle_url('/'),
                    get_string('recordmissingerror', 'local_registration'),
                    null,
                    'error'
                );
            }

            // Has the user been notified?
            if ((int) $record->approved !== manager::REGISTRATION_NOTIFIED) {
                redirect(
                    new moodle_url('/'),
                    get_string('notifiedaccesserror', 'local_registration'),
                    null,
                    'error'
                );
            }

            // Get the hash from the url.
            $hash = optional_param('hash', 0, PARAM_RAW);
            if (empty($hash)) {
                redirect(
                    new moodle_url('/'),
                    get_string('hashmissingerror', 'local_registration'),
                    null,
                    'error'
                );
            }

            // Validate hash.
            if ($encryptor->decrypt($hash) !== $record->email) {
                redirect(
                    new moodle_url('/'),
                    get_string('hashmissingerror', 'local_registration'),
                    null,
                    'error'
                );
            }

            // Save data to session.
            $SESSION->local_registration['id'] = $id;
            $SESSION->local_registration['hash'] = $hash;
            $tenantid = $SESSION->local_registration['tenantid'] = $record->tenantid;
            $firstname = $SESSION->local_registration['firstname'] = $record->firstname;
            $lastname = $SESSION->local_registration['lastname'] = $record->lastname;
            $email = $SESSION->local_registration['email'] = $record->email;
            $country = $SESSION->local_registration['country'] = $record->country;
            $gender = $SESSION->local_registration['gender'] = $record->gender;
            $position = $SESSION->local_registration['position'] = $record->position;
            $domain = $SESSION->local_registration['domain'] = $record->domain;
            $comments = $SESSION->local_registration['comments'] = $record->comments;
            $interests = $SESSION->local_registration['interests'] =
                !empty($record->interests) ? json_decode($record->interests, true) : [];
            $policies = $SESSION->local_registration['policies'] = 1;
        } else {
            // Get the tenant id from the url.
            $tenantid = optional_param('tenantid', 0, PARAM_INT);
            if (!$tenantid) {
                redirect(
                    new moodle_url('/'),
                    get_string('tenantidmissingerror', 'local_registration'),
                    null,
                    'error'
                );
            }

            // Check if tenant exists.
            if (!$manager->tenant_exists($tenantid)) {
                redirect(
                    new moodle_url('/'),
                    get_string('tenantidinvaliderror', 'local_registration'),
                    null,
                    'error'
                );
            }

            // Get data from session.
            $id = $SESSION->local_registration['id'] ?? 0;
            $hash = $SESSION->local_registration['hash'] ?? '';
            $tenantid = $SESSION->local_registration['tenantid'] ?? $tenantid;
            $firstname = $SESSION->local_registration['firstname'] ?? '';
            $lastname = $SESSION->local_registration['lastname'] ?? '';
            $email = $SESSION->local_registration['email'] ?? '';
            $country = $SESSION->local_registration['country'] ?? '';
            $gender = $SESSION->local_registration['gender'] ?? '';
            $position = $SESSION->local_registration['position'] ?? '';
            $domain = $SESSION->local_registration['domain'] ?? '';
            $comments = $SESSION->local_registration['comments'] ?? '';
            $interests = $SESSION->local_registration['interests'] ?? '';
            $policies = $SESSION->local_registration['policies'] ?? '';
        }

        // ID.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $this->set_data(['id', $id]);

        // Hash.
        $mform->addElement('hidden', 'hash', $hash);
        $mform->setType('hash', PARAM_RAW);
        $this->set_data(['hash', $hash]);

        // Tenant id.
        $mform->addElement('hidden', 'tenantid', $tenantid);
        $mform->setType('tenantid', PARAM_INT);
        $this->set_data(['tenantid', $tenantid]);

        // First name.
        $mform->addElement('text', 'firstname', get_string('firstname', 'local_registration'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', get_string('fieldempty', 'local_registration'), 'required', null, 'client');
        $mform->addRule(
            'firstname',
            get_string('maxlength', 'local_registration', self::FIELD_MAX_LENGTH),
            'maxlength',
            self::FIELD_MAX_LENGTH,
            'client'
        );
        $mform->addHelpButton('firstname', 'firstname', 'local_registration');
        $this->set_data(['firstname' => $firstname]);

        // Last name.
        $mform->addElement('text', 'lastname', get_string('lastname', 'local_registration'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', get_string('fieldempty', 'local_registration'), 'required', null, 'client');
        $mform->addRule(
            'lastname',
            get_string('maxlength', 'local_registration', self::FIELD_MAX_LENGTH),
            'maxlength',
            self::FIELD_MAX_LENGTH,
            'client'
        );
        $mform->addHelpButton('lastname', 'lastname', 'local_registration');
        $this->set_data(['lastname' => $lastname]);

        // Email.
        $mform->addElement('text', 'email', get_string('email', 'local_registration'));
        $mform->setType('email', PARAM_NOTAGS);
        $mform->addRule('email', get_string('emailempty', 'local_registration'), 'required', null, 'client');
        $mform->addRule(
            'email',
            get_string('maxlength', 'local_registration', self::FIELD_MAX_LENGTH),
            'maxlength',
            self::FIELD_MAX_LENGTH,
            'client'
        );
        $mform->addRule('email', get_string('emailinvalid', 'local_registration'), 'email', null, 'client');
        $mform->addHelpButton('email', 'email', 'local_registration');
        $this->set_data(['email' => $email]);
        if ($id) {
            $mform->hardFreeze('email');
        }

        // Country.
        $countries = get_string_manager()->get_list_of_countries();
        $countries = ['' => get_string('selectacountry')] + $countries;
        $mform->addElement('select', 'country', get_string('country', 'local_registration'), $countries);
        $mform->setType('country', PARAM_TEXT);
        $mform->addRule('country', get_string('countryempty', 'local_registration'), 'required', null, 'client');
        $mform->addHelpButton('country', 'country', 'local_registration');
        $this->set_data(['country' => $country]);

        // Gender.
        $genders = $manager->get_profile_field_options('gender') ?? [];
        $genders = ['' => get_string('select', 'local_registration')] + $genders;
        $mform->addElement('select', 'gender', get_string('gender', 'local_registration'), $genders);
        $mform->addRule('gender', get_string('genderempty', 'local_registration'), 'required', null, 'client');
        $mform->addHelpButton('gender', 'gender', 'local_registration');
        if ($gender) {
            $this->set_data(['gender' => $gender]);
        } else {
            $mform->setDefault('gender', get_string('select', 'local_registration'));
        }

        // Position.
        $mform->addElement('text', 'position', get_string('position', 'local_registration'));
        $mform->setType('position', PARAM_TEXT);
        $mform->addRule('position', get_string('positionempty', 'local_registration'), 'required', null, 'client');
        $mform->addRule(
            'position',
            get_string('maxlength', 'local_registration', self::FIELD_MAX_LENGTH),
            'maxlength',
            self::FIELD_MAX_LENGTH,
            'client'
        );
        $mform->addHelpButton('position', 'position', 'local_registration');
        $this->set_data(['position' => $position]);

        // Domain.
        $domains = $manager->get_profile_field_options('domain') ?? [];
        $domains = ['' => get_string('select', 'local_registration')] + $domains;
        $mform->addElement('select', 'domain', get_string('domain', 'local_registration'), $domains);
        $mform->addRule('domain', get_string('domainempty', 'local_registration'), 'required', null, 'client');
        $mform->addRule('domain', get_string(
            'maxlength',
            'local_registration',
            self::FIELD_MAX_LENGTH
        ), 'maxlength', self::FIELD_MAX_LENGTH, 'client');
        $mform->addHelpButton('domain', 'domain', 'local_registration');
        if ($domain) {
            $this->set_data(['domain' => $domain]);
        } else {
            $mform->setDefault('domain', get_string('select', 'local_registration'));
        }

        // Comments.
        $mform->addElement(
            'textarea',
            'comments',
            get_string('comments', 'local_registration'),
            'wrap="virtual" rows="4" cols="100",maxlength=500'
        );
        $mform->setType('comments', PARAM_NOTAGS);
        $mform->addRule('comments', get_string('maxlength500', 'local_registration'), 'maxlength', 500, 'client');
        $mform->addHelpButton('comments', 'comments', 'local_registration');
        $this->set_data(['comments' => $comments]);

        // Fields of interest.
        $interestsoptions = $manager->get_profile_field_options('interests') ?? [];
        $interestsoptions = ['' => get_string('select', 'local_registration')] + $interestsoptions;
        $selectinterests = $mform->addElement(
            'select',
            'interests',
            get_string('interests', 'local_registration'),
            $interestsoptions
        );
        $selectinterests->setMultiple(true);
        $selectinterests->setSize(5);
        $mform->addRule('interests', get_string('interestsempty', 'local_registration'), 'required', null, 'client');
        $mform->addHelpButton('interests', 'interests', 'local_registration');
        if ($interests) {
            $this->set_data(['interests' => $interests]);
        } else {
            $mform->setDefault('interests', get_string('select', 'local_registration'));
        }

        // Policies.
        $policieshtml = $manager->format_policies($manager->get_policies());

        $mform->addElement(
            'checkbox',
            'policies',
            '',
            get_string('policies', 'local_registration', $policieshtml)
        );
        $mform->addRule('policies', get_string('policiesempty', 'local_registration'), 'required', null, 'client');
        $this->set_data(['policies' => $policies]);

        // Action buttons.
        $buttons = [];
        $buttons[] = &$mform->createElement(
            'submit',
            'submitbutton',
            get_string('continue')
        );
        $buttons[] = &$mform->createElement('cancel');
        $mform->addGroup($buttons, 'buttons', '', [' '], false);
    }

    /**
     * Validate the data from the form.
     *
     * @param  array $data The form data.
     * @param  array $files The form files.
     *
     * @return array An array of error messages.
     */
    public function validation($data, $files) {
        global $SESSION;

        $manager = new manager();

        $errors = parent::validation($data, $files);

        if (\core_text::strlen($data['firstname']) > self::FIELD_MAX_LENGTH) {
            $errors['firstname'] = get_string('firstnamelengtherror', 'local_registration', self::FIELD_MAX_LENGTH);
        }

        if (\core_text::strlen($data['lastname']) > self::FIELD_MAX_LENGTH) {
            $errors['lastname'] = get_string('lastnamelengtherror', 'local_registration', self::FIELD_MAX_LENGTH);
        }

        if ($data['email'] && !validate_email($data['email'])) {
            $errors['email'] = get_string('emailinvalid', 'local_registration');
        }

        if (empty($errors['email'])) {
            if (\core_text::strlen($data['email']) > self::FIELD_MAX_LENGTH) {
                $errors['email'] = get_string('emaillengtherror', 'local_registration', self::FIELD_MAX_LENGTH);
            }
        }

        $recordbyid = !empty($SESSION->local_registration['id']) ?
            $manager->get_registration_record($SESSION->local_registration['id']) :
            0;

        if (!$recordbyid || ($recordbyid && $recordbyid->email !== $data['email'])) {
            // Check that user does not exist in 'user' (by email).
            if (empty($errors['email'])) {
                if (\core_user::get_user_by_email($data['email'])) {
                    $errors['email'] = get_string('erroruserexists', 'local_registration');
                }
            }

            // Check that email is unique in 'local_registration'.
            if (empty($errors['email'])) {
                $record = $manager->get_registration_record(null, $data['email']);

                if (!empty($record)) {
                    $approved = $record->approved;

                    switch ($approved) {
                        case manager::REGISTRATION_NOTIFIED:
                        case manager::REGISTRATION_PENDING:
                        case manager::REGISTRATION_APPROVED:
                            $errors['email'] = get_string('erroremailexists', 'local_registration');
                            break;
                        case manager::REGISTRATION_REJECTED:
                            $errors['email'] = get_string('erroremailrejected', 'local_registration');
                            break;
                        default:
                            $errors['email'] = get_string('erroremailexists', 'local_registration');
                            break;
                    }
                }
            }
        }

        if (\core_text::strlen($data['country']) > self::FIELD_MAX_LENGTH) {
            $errors['country'] = get_string('countrylengtherror', 'local_registration', self::FIELD_MAX_LENGTH);
        }

        if (\core_text::strlen($data['gender']) > self::FIELD_MAX_LENGTH) {
            $errors['gender'] = get_string('genderlengtherror', 'local_registration', self::FIELD_MAX_LENGTH);
        }

        if (\core_text::strlen($data['position']) > self::FIELD_MAX_LENGTH) {
            $errors['position'] = get_string('positionlengtherror', 'local_registration', self::FIELD_MAX_LENGTH);
        }

        if (\core_text::strlen($data['domain']) > self::FIELD_MAX_LENGTH) {
            $errors['domain'] = get_string('domainlengtherror', 'local_registration', self::FIELD_MAX_LENGTH);
        }

        if (\core_text::strlen($data['comments']) > self::FIELD_MAX_LENGTH) {
            $errors['comments'] = get_string('commentslengtherror', 'local_registration', self::FIELD_MAX_LENGTH);
        }

        return $errors;
    }
}
