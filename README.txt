README.txt for Legal Module
===========================

A module which displays your Terms & Conditions to users who want to register, and makes sure they accept the T&C before their registration is accepted.

Note:
If this module is installed, but no T&C text has been entered, this module has no effect.

** WARNING **
=========
If you're upgrading from a previous version copy your terms and conditions to your hard drive, i.e. into a document, before upgrading. You will need to paste them back into Drupal.

Requirements
============

This module requires Drupal 4.5.x



Installation
============

1. Create the database tables using the sql in legal.sql

2. In the Drupal modules directory create a directory called "legal", put the files legal.module and legal.css into the directory.

3. Log in to your Drupal site as the Admin user, and go to the administer section

4. Go to modules

5. Tick the check-box for legal, then click the 'Save Configuration' button at the bottom.


Configuration
============

1. Go to settings --> legal

2. Input your terms & conditions text, set how you would like it displayed

- Scroll Box -
Standard form text box (read only)
Text is entered and displayed as text only

- Scroll Box (CSS) -
Scrollable text box created in CSS
Text should be entered with HTML formatting

- HTML Text -
Terms & conditions displayed as HTML formatted text
Text should be entered with HTML formatting

Bugs
============

* Existing users can still log in without needing to accept terms & conditions

* Distributed authentication bypasses terms & conditions.

* If admin updates terms & conditions, users who have accepted old terms & conditions will see the new terms & conditions



To Do
============

- Prevent existing users and distributed authentication users logging in until they have agreed to terms & conditions

- Prevent existing users logging in until they have accepted new terms & conditions (if admin has updated terms & conditions)

- Provide a standard T&C (are there any opensource T&Cs for forums and such like?)


Author
============

Please send all feedback and code improvements to me:

Robert Castelo (MegaGrunt) <robertcastelo@cortextcommunications.com>
