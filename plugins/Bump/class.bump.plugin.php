<?php

class BumpPlugin extends Gdn_Plugin {
    /**
     * Add bump option to discussion options.
     *
     * @param GardenController $sender Sending controller instance.
     * @param array            $args   Event arguments.
     *
     * @return void.
     */
    public function base_discussionOptions_handler($sender, $args) {
        $discussion = $args['Discussion'];
        if (checkPermission('Garden.Moderation.Manage')) {
            $label = t('Bump');
            $url = url("discussion/bump?discussionid={$discussion->DiscussionID}", true);
            // Deal with inconsistencies in how options are passed
            if (isset($sender->Options)) {
                $sender->Options .= wrap(anchor($label, $url, 'Bump Hijack'), 'li');
            } else {
                $args['DiscussionOptions']['Bump'] = [
                    'Label' => $label,
                    'Url' => $url,
                    'Class' => 'Bump Hijack'
                ];
            }
        }
    }

    /**
     * Handle discussion option menu bump action.
     *
     * @param DiscussionController $sender Sending controller instance.
     * @param array                $args   Event arguments.
     *
     * @return void.
     */
    public function discussionController_bump_create($sender, $args) {
        $sender->permission('Garden.Moderation.Manage');
        if ($sender->Form->authenticatedPostBack()) {
            // Get discussion
            $discussionID = $sender->Request->get('discussionid');
            $discussion = $sender->DiscussionModel->getID($discussionID);
            if (!$discussion) {
                throw notFoundException('Discussion');
            }
            // Update DateLastComment & redirect
            $sender->DiscussionModel->setField($discussionID, 'DateLastComment', Gdn_Format::toDateTime());
            $sender->jsonTarget('', '', 'Refresh');
            $sender->render('Blank', 'Utility', 'Dashboard');
        }
    }
}
