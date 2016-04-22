<?php

return [

    // Flowdock channel defaults
    'source_tokens' => [
        '' => '',
    ],
    'user' => 'Rocketeer',
    'application' => '', // Leave blank to use default Rocketeer 'application_name'
    'default_branch' => 'develop',
    'thread_title' => 'Rocketeer Deployment',

    // Message
    // You can use the following variables :
    // :user = User deploying
    // :branch = Branch
    // :repo = Repository
    // :conn = Connection and stage
    'deploy_before'   => ':user started deploying branch ":branch" on :repo :conn',
    'deploy_after'    => ':user finished deploying branch ":branch" on :repo :conn',
    'rollback_before' => ':user started rolling back branch ":branch" on :repo :conn',
    'rollback_after'  => ':user finished rolling back branch ":branch" on :repo :conn',

];