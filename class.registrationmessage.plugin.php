<?php if (!defined('APPLICATION')) exit();

$PluginInfo['registrationMessage'] = array(
    'Name' => 'Registration Message',
    'Description' => 'Sends a configurable message users immediately after registration.',
    'Version' => '0.1',
    'MobileFriendly' => true,
    'SettingsUrl' => 'settings/registrationmessage',
    'SettingsPermission' => 'Garden.Settings.Manage',
    'Author' => 'Bleistivt',
    'AuthorUrl' => 'http://bleistivt.net',
    'License' => 'GNU GPL2'
);

class registrationMessagePlugin extends Gdn_Plugin {

    public function entryController_registrationSuccessful_handler($sender) {
        if (!C('EnabledApplications.Conversations')) {
            return;
        }

        $model = new ConversationModel();
        $messageModel = new ConversationMessageModel();
        $model->save([
            'Body' => C('registrationMessage.message'),
            'Format' => 'Html',
            'InsertUserID' => C('registrationMessage.user', Gdn::UserModel()->getSystemUserID()),
            'RecipientUserID' => array($sender->UserModel->EventArguments['User']->UserID);
        ], $messageModel);
    }

    public function settingsController_registrationMessage_create($sender) {
        $sender->permission('Garden.Settings.Manage');
        $sender->addSideMenu('settings/registrationmessage');

        $conf = new ConfigurationModule($sender);
        $conf->initialize(array(
            'registrationMessage.message' => array(
                'Control' => 'textbox',
                'LabelCode' => 'Write a message to send users on registration.)',
                'Options' => array('MultiLine' => true)
            )
        ));

        $sender->setData('Title', 'Registration Message');
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
        if (!C('registrationMessage.message')) {
            saveToConfig('registrationMessage.message', 'Welcome to the community!');
        }
    }

}
