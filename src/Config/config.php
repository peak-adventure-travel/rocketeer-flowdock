<?php

return [

    // Flowdock channel
    'source_tokens' => array(
        '' => '',
    ),

    // Message
    // You can use the following variables :
    // 1: User deploying
    // 2: Branch
    // 3: Connection and stage
    // 4: Host
    'deploy_before'   => '{1} started deploying branch "{2}" on "{3}" ({4}) :rocket:',
    'deploy_after'    => '{1} finished deploying branch "{2}" on "{3}" ({4}) :rocket:',
    'rollback_before' => '{1} started rolling back branch "{2}" on "{3}" ({4}) :rocket:',
    'rollback_after'  => '{1} finished rolling back branch "{2}" on "{3}" ({4}) :rocket:',

];