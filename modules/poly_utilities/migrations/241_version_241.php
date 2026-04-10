<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_241 extends App_module_migration
{
    public function up()
    {
        // Perform database upgrade here
    }
    public function down()
    {
        // Perform database downgrade here
    }
    public function logChanged()
    {
        /*
            -------- 2.4.1 (November 3, 2024) --------
            P/S: In case you encounter any conflicts during usage, please leave feedback or contact me at polyxgo@gmail.com. I will support you right away! Thanks.

            NEW
            - Custom menu item type: Popup. Supports displaying custom HTML content across all 3 levels of the Sidebar menu (Setup menu and Clients to be updated later). Admins can create menus assigned to specific user accounts, clients, or roles with access permissions to view the menu content. Example: a reward announcement board for personnel in the 'marketing roles' group (marketing department).
            - Custom JavaScript, CSS: integrated permission locking for editing each custom JavaScript and CSS code. Administrator with ID 1 can lock custom JavaScript and CSS code, preventing edits from other admins. This feature helps administrators ensure that custom scripts and CSS for the Perfex CRM interface remain unchanged.

            UPDATED
            - Support for drag-and-drop to adjust the display position of the All-in-One Contact button icon.
            - Support for drag-and-drop to adjust the display position of the Scroll to Top button on the page.
            - Add languages and translations: Russian, Bulgarian, Greek.

            FIXED
            - Temporarily removed hook event handling in $ajax & fetch setup from routes containing the slug /chat. Handling may conflict with modules that also hook into this event, causing one of the two modules to malfunction. Other routes remain unaffected.
            - Fixed overlapping issues when setting the positions of the All-in-One Contact button and Scroll to Top button. This occurs on sites using additional modules with fixed icons on the page, such as chat plugins like WhatsApp.
        */
    }
}
