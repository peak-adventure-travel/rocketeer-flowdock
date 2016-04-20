<?php

return [

    // Flowdock channel
    'source_tokens' => array(
        '' => '',
    ),
    'user' => 'Rocketeer',
    'application' => '', // Leave blank to use default Rocketeer 'application_name'
    'title' => 'Rocketeer Deployment',
    'thread_title' => 'Rocketeer Deployment',

    // Message
    // You can use the following variables :
    // 1: User deploying
    // 2: Branch
    // 3: Repository
    // 4: Connection and stage
    'deploy_before'   => '{1} started deploying branch "{2}" on "{3} {4}" :rocket:',
    'deploy_after'    => '{1} finished deploying branch "{2}" on "{3} {4}" :rocket:',
    'rollback_before' => '{1} started rolling back branch "{2}" on "{3} {4}" :rocket:',
    'rollback_after'  => '{1} finished rolling back branch "{2}" on "{3} {4}" :rocket:',

];