Ying Wa PTA Website
===================

How To Setup
------------
* Assume Apache+PHP+MySQL installed
* Clone the source code to the new environment
* Symlink the Apache root to the wordpress sub-directory
* Manually edit wordpress/wp-config.php to point to the database
* Restore the initial database from sql/wordpress.sql
* Run scripts/fixperms.sh

This would give you an initial setup (e.g. menus) for the website without content.


