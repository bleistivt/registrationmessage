<?php

$PluginInfo['registrationmessage'] = [
    'Name' => 'Registration Message',
    'Description' => 'Sends a configurable message to users immediately after registration.',
    'Version' => '0.2',
    'RequiredApplications' => ['Vanilla' => '2.2'],
    'MobileFriendly' => true,
    'SettingsUrl' => 'settings/registrationmessage',
    'SettingsPermission' => 'Garden.Settings.Manage',
    'Author' => 'Bleistivt',
    'AuthorUrl' => 'http://bleistivt.net',
    'License' => 'GNU GPL2'
];

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
        ], new ConversationMessageModel());
    }


    public function settingsController_registrationMessage_create($sender) {
        $sender->permission('Garden.Settings.Manage');
        $sender->addSideMenu('settings/registrationmessage');

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


    public function base_getAppSettingsMenuItems_handler($sender, &$args) {
        $args['SideMenu']->addLink(
          'Users',
          'Registration Message',
          'settings/registrationmessage',
          'Garden.Settings.Manage'
        );
    }


    public function setup() {
        if (!c('RegistrationMessage.Message')) {
            saveToConfig('RegistrationMessage.Message', 'Hi %%NAME%%, welcome to the community!');
        }
    }

}
