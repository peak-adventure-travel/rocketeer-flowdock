<?php

return [

    // Flowdock
    // Flow source tokens, follow README for how to generate these
    'source_tokens' => [
        '' => '',
    ],
    // Leave the below blank to use default Rocketeer 'application_name'
    'application' => '',
    'branch' => 'develop',
    'thread_title' => 'Rocketeer Deployment',

    // Deployment
    // You can use any of the *.before, *.after or *.halt for any hooks (ie. setup, deploy, cleanup) :
    'stage_before' => 'deploy.before',
    'stage_after' => 'deploy.after',
    'stage_rollback' => 'deploy.halt',

    // Message
    // You can use the following variables :
    // :user = User deploying
    // :branch = Branch
    // :repo = Repository
    // :conn = Connection and stage
    'message_before' => ':user started deploying branch ":branch" on :repo :conn',
    'message_after' => ':user finished deploying branch ":branch" on :repo :conn',
    'message_rollback' => 'Rolling back branch ":branch" on :repo :conn, view the rocketeer log file for more details',

];