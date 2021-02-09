<?php

class RegistrationMessagePlugin extends Gdn_Plugin {

    public function userModel_afterRegister_handler($sender, $args) {
        if (!Gdn::config('EnabledApplications.Conversations')) {
            return;
        }

        $name = Gdn::userModel()->getID($args['UserID'])->Name;

        (new ConversationModel())->save([
            'Body' => str_replace('%%NAME%%', $name, Gdn::config('RegistrationMessage.Message')),
            'Format' => 'Html',
            'InsertUserID' => Gdn::config('RegistrationMessage.User', Gdn::userModel()->getSystemUserID()),
            'RecipientUserID' => [$args['UserID']],
            // This should not be needed. See https://open.vanillaforums.com/discussion/37984
            'UpdateUserID' => Gdn::session()->UserID
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
