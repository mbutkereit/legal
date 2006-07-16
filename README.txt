********************************************************************
D R U P A L    M O D U L E
********************************************************************
Name: Legal module Author: Robert Castelo <services at cortextcommunications dot com> 
Drupal: 4.7.x
********************************************************************
DESCRIPTION:

    Displays your Terms & Conditions to users who want to register, 
    and makes sure they accept the T&C before their registration
    is accepted.
    
    Each time a new version of the T&C is saved all users will be required to 
    accept the new version. A record is kept in the database for each user, 
    recording which version of the T&C was accepted and when.

    An "I accept T&C" checkbox is automatically added, and up to 5 additional 
    checkboxes can also be added as part of the T&C.

    A list of changes can be added each time the T&C is saved, users will see all
    changes listed since they last accepted T&C.

    Note: No T&C will be displayed until the T&C text has been input by
    the administrator.


********************************************************************
INSTALLATION:

    Note: It is assumed that you have Drupal up and running.  Be sure to
    check the Drupal web site if you need assistance.  If you run into
    problems, you should always read the INSTALL.txt that comes with the
    Drupal package and read the online documentation.

	1. Place the entire legal directory into your Drupal modules/directory.

	2. Enable the legal module by navigating to:

	   administer > modules

	Click the 'Save configuration' button at the bottom to commit your
      changes.

	3. If you'r updating from a 4.6 version of the module, using your
          browser navigate to your-domain/update.php, and run legal update 1


********************************************************************
CONFIGURATION

	1. Go to admin -> access control
	    
	    Set which roles can "view Terms and Conditions"
	    Set which roles can "administer Terms and Conditions"
	
	2. Go to settings --> legal

	   Input your terms & conditions text, set how you would like it
        displayed

	- Scroll Box - Standard form text box (read only) Text is entered
       and displayed as text only

	- Scroll Box (CSS) - Scrollable text box created in CSS Text should
       be entered with HTML formatting

	- HTML Text - Terms & conditions displayed as HTML formatted text
       Text should be entered with HTML formatting

********************************************************************
SPONSORSHIP

===========================
LULLABOT
===========================

	Big thank you to Raven Brooks at Lullabot for sponsoring:

		4.6 => 4.7 Update
		Version control
		Additional checkboxes
		Change listing

	http://www.lullabot.com

