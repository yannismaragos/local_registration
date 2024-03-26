<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_registration
 * @category    string
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'User registration';
$string['plugindescription'] = 'User registration plugin description';

// Errors.
$string['viewerror'] = 'View not found.';
$string['taskerror'] = 'Task not found.';
$string['errorcontrollergetname'] = 'Controller not found for the given name: {$a->name}.';

// Registration form.
$string['formtitle'] = 'Registration form';
$string['formdescription'] = 'Registration form description text.';
$string['firstname'] = 'First name';
$string['firstname_help'] = 'Enter your first name.';
$string['lastname'] = 'Last name';
$string['lastname_help'] = 'Enter your last name.';
$string['email'] = 'Email';
$string['email_help'] = 'Enter your email.';
$string['country'] = 'Country';
$string['country_help'] = 'Select a country.';
$string['gender'] = 'Gender';
$string['gender_help'] = 'Select your gender.';
$string['position'] = 'Position';
$string['position_help'] = 'Enter your position.';
$string['domain'] = 'Domain';
$string['domain_help'] = 'Select a domain.';
$string['comments'] = 'Comments';
$string['comments_help'] = 'Enter additional comments regarding your registration.';
$string['interests'] = 'Fields of interest';
$string['interests_help'] = 'Choose your fields of interest from the drop-down menu.';
$string['select'] = 'Select';
$string['policies'] = 'I have reviewed and hereby acknowledge my acceptance of the following documents: {$a}.';
$string['registrationstarted'] = 'Your registration process has begun. Please check your inbox for a confirmation email.
Click on the provided link to confirm your email within the next {$a} hours. After this period your registration will be discarded and you will need to register again.';
$string['registrationupdated'] = 'Your registration data has been successfully updated.';
$string['confirmationemailsent'] = 'Confirmation email sent';
$string['alreadyregistered'] = 'This email address is already registered. If you have an account, please log in';

// Field validations.
$string['fieldempty'] = 'This field cannot be empty.';
$string['emailempty'] = 'Please enter your email.';
$string['emailinvalid'] = 'Please enter a valid email address.';
$string['countryempty'] = 'Please select a country.';
$string['genderempty'] = 'Please select a gender.';
$string['positionempty'] = 'Please enter your position.';
$string['domainempty'] = 'Please select a domain.';
$string['interestsempty'] = 'Please select a field of interest.';
$string['maxlength'] = 'Please limit your input to a maximum of {$a} characters.';
$string['maxlength500'] = 'Please limit your input to a maximum of 500 characters.';
$string['policiesempty'] = 'Please review and acknowledge acceptance of the documents provided above.';
$string['firstnamelengtherror'] = 'First name length should not exceed {$a} characters.';
$string['lastnamelengtherror'] = 'Last name length should not exceed {$a} characters.';
$string['emaillengtherror'] = 'Email length should not exceed {$a} characters.';
$string['countrylengtherror'] = 'Country length should not exceed {$a} characters.';
$string['genderlengtherror'] = 'Gender length should not exceed {$a} characters.';
$string['positionlengtherror'] = 'Position length should not exceed {$a} characters.';
$string['domainlengtherror'] = 'Domain length should not exceed {$a} characters.';
$string['commentslengtherror'] = 'Comments length should not exceed 500 characters.';
$string['interestslengtherror'] = 'Interests length should not exceed {$a} characters.';
$string['erroruserexists'] = 'The email provided is already associated with an existing account. If you have an account, please log in.';
$string['erroremailexists'] = 'The email provided is already associated with an existing record.';
$string['erroremailrejected'] = 'The email provided has been rejected.';

// Action buttons.
$string['register'] = 'Register';
$string['edit'] = 'Edit';
$string['submit'] = 'Submit';

// Review page.
$string['reviewdatatitle'] = 'Review registration data';

// Confirm page.
$string['confirmtitle'] = 'Email confirmation';
$string['emailconfirmed'] = 'Your registration has been confirmed. Please await approval from your manager before accessing the platform.';
$string['emailconfirmedtrusted'] = 'Your registration is now confirmed, and your user account has been successfully created. ' .
    'An email has been sent to your address, confirming the registration and providing a temporary password. ' .
    'Please ensure to update this password during your initial login to the platform.';
$string['emailalreadyconfirmed'] = 'Your registration has already been confirmed.';
$string['errorinvalidhash'] = 'Invalid confirmation link. The provided hash value is incorrect or has expired.';
$string['erroremailconfirm'] = 'There was an error while confirming your registration. Please try again later or contact support for assistance.';

// Messages.
$string['recordmissingerror'] = 'The URL did not include a valid record id';
$string['hashmissingerror'] = 'The URL did not include a valid hash';
$string['tenantidmissingerror'] = 'The URL did not include a tenant id.';
$string['tenantidinvaliderror'] = 'The URL did not include a valid tenant id.';
$string['emptysessiondata'] = 'Session data is empty. Please try again.';
$string['notifiedaccesserror'] = 'You do not have the necessary permissions to edit this form.';

// Misc.
$string['workplacemenuitemtitle'] = 'User registration';

// Settings.
$string['config:unconfirmedhours'] = 'Unconfirmed registrations retention';
$string['config:unconfirmedhours_desc'] = 'Specify the time frame, in hours, for automatically removing unconfirmed registrations from the database.';
$string['config:preapproveddomains'] = 'Pre-approved email domains';
$string['config:preapproveddomains_desc'] = 'Enter pre-approved email domains, one per line. Users with email addresses from these domains will be automatically approved by the scheduled task.';

// Emails.
$string['emailconfirmationsubject'] = '{$a}: Account confirmation.';
$string['emailconfirmation'] = 'Hi {$a->firstname},<br><br>
We\'ve received a request for a new account at \'{$a->sitename}\' using your email address.<br><br>
To verify your email, please visit the following web address within the next {$a->unconfirmedhours} hours:<br><br>
{$a->link}<br><br>
In most email clients, this link should be clickable and will lead you to the confirmation page on our website. ' .
    'If that doesn\'t work, we recommend copying the link and pasting it into your web browser.
<br><br>
Please be aware that your access to the website is pending approval from an administrator. ' .
    'Once approved, you will receive a confirmation email containing a temporary password. ' .
    'Remember to change this password upon your first login to the platform.<br><br>
For any additional assistance, feel free to reach out to our Support Team.<br><br>
Best regards,
{$a->admin}';
$string['emailrejectsubject'] = '{$a}: Account rejection.';
$string['emailrejectbody'] = 'Hi {$a->firstname},<br><br>
We regret to inform you that your registration application has been declined.<br><br>
Reason for rejection:<br>
{$a->reason}
<br><br>
Best regards,
{$a->admin}';
$string['emailnotifysubject'] = '{$a}: Update your application.';
$string['emailnotifybody'] = 'Hi {$a->firstname},<br><br>
We appreciate your interest in our platform. Your registration application is currently pending.<br><br>
Please update your application by clicking on the following link:<br><br>
{$a->link}<br><br>
In most email clients, this link should be clickable and will lead you to the form edit page on our website. ' .
    'If that doesn\'t work, we recommend copying the link and pasting it into your web browser.
<br><br>
Reason for update:<br>
{$a->reason}
<br><br>
Best regards,
{$a->admin}';

// Datatables.
$string['userstitle'] = 'Pending User Registrations';
$string['users'] = 'Pending User Registrations';

$string['columnfirstname'] = 'First name';
$string['columnlastname'] = 'Last name';
$string['columnemail'] = 'Email';
$string['columntenantname'] = 'Tenant';
$string['columncountry'] = 'Country';
$string['columngender'] = 'Gender';
$string['columnposition'] = 'Position';
$string['columndomain'] = 'Domain';
$string['columncomments'] = 'Comments';
$string['columninterests'] = 'Fields of interest';
$string['columnconfirmed'] = 'Confirmed';
$string['columntimecreated'] = 'Created';
$string['assessor'] = 'Assessor';
$string['duplicate'] = 'Duplicate of user: ';
$string['notified'] = 'Notified';
$string['actions'] = 'Actions';
$string['approve'] = 'Approve';
$string['reject'] = 'Reject';
$string['rejectreason'] = 'Enter reason for rejection.';
$string['notify'] = 'Notify';
$string['notifyreason'] = 'Enter reason for notification.';

// Tenant notifications.
$string['messageprovider:notifytenants'] = 'Notify tenants for user confirmation/update';
$string['notifytenant:subjectconfirm'] = 'New user verified';
$string['notifytenant:bodyconfirm'] = 'A user in your tenant, for which you are the administrator, has successfully verified their email address.';
$string['notifytenant:subjectupdate'] = 'User updated their registration application';
$string['notifytenant:bodyupdate'] = 'A user in your tenant, for which you are the administrator, has updated their registration application.';
$string['notifytenant:contexturlname'] = 'Pending User Registrations';

// Capabilities.
$string['registration:view_all_pending'] = 'View all pending registrations';
$string['registration:view_pending'] = 'View pending registrations';
$string['errorcapability'] = 'You dont have the required permissions to view this content.';

// Modals.
$string['modal:approvetitle'] = 'Approve user';
$string['modal:approvedesc'] = 'Are you sure you want to approve this user?';
$string['modal:rejecttitle'] = 'Reject user';
$string['modal:notifytitle'] = 'Notify user';
$string['modal:reasonempty'] = 'This field cannot be empty.';
$string['reasonlengtherror'] = 'Text should not exceed 500 characters.';
$string['modal:rejectreason'] = 'Reason';
$string['modal:rejectreason_help'] = 'Enter reason for rejection.';
$string['modal:notifyreason'] = 'Reason';
$string['modal:notifyreason_help'] = 'Enter reason for notification.';
$string['modal:approvesuccess'] = 'The registration has been approved successfully.';
$string['modal:rejectsuccess'] = 'The registration has been rejected.';
$string['modal:notifysuccess'] = 'The user has received a notification to revise their application.';
$string['modal:errorcreatinguser'] = 'An error occurred while attempting to approve the user. Please try again.';
$string['modal:errorrejectinguser'] = 'An error occurred while attempting to reject the user. Please try again.';
$string['modal:errornotifyinguser'] = 'An error occurred while attempting to notify the user. Please try again.';

// Task.
$string['expirationcontroltask'] = 'Expiration control task';
$string['expirationcontroltaskdeleterecords'] = 'The following record ids were deleted:';

// Privacy.
$string['privacy:metadata:local_registration:users'] = 'The list of users.';
$string['privacy:metadata:local_registration:firstname'] = 'The first name of the user.';
$string['privacy:metadata:local_registration:lastname'] = 'The last name of the user.';
$string['privacy:metadata:local_registration:email'] = 'The email of the user.';

// Countries select list.
$string['withselectedcountries'] = 'With selected countries';
