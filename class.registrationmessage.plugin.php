<?php

class RegistrationMessagePlugin extends Gdn_Plugin {

    public function userModel_afterRegister_handler($sender, $args) {
        if (!c('EnabledApplications.Conversations')) {
            return;
        }

        $name = val('Name', Gdn::userModel()->getID($args['UserID']));

        (new ConversationModel())->save([
            'Body' => str_replace('%%NAME%%', $name, c('RegistrationMessage.Message')),
            'Format' => 'Html',
            'InsertUserID' => c('RegistrationMessage.User', Gdn::userModel()->getSystemUserID()),
            'RecipientUserID' => [$args['UserID']]
        ]);
    }


    public function settingsController_registrationMessage_create($sender) {
        $sender->permission('Garden.Settings.Manage');

        $conf = new ConfigurationModule($sender);
        $conf->initialize([
            'RegistrationMessage.Message' => [
                'Control' => 'textbox',
                'LabelCode' => 'Write a message to send to newly registered users.',
                'Description' => 'HTML is allowed.  You can use <code>%%NAME%%</code> as a placeholder for the user\'s name.',
                'Options' => ['MultiLine' => true]
            ]
        ]);

        $sender->title('Registration Message');
        $conf->renderAll();
    }


    public function setup() {
        touchConfig('RegistrationMessage.Message', 'Hi %%NAME%%, welcome to the community!');
    }

}
