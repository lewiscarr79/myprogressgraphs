<?php
function block_myprogressgraphs_add_block_instance() {
    global $DB;

    $block = new stdClass();
    $block->blockname = 'myprogressgraphs';
    $block->parentcontextid = CONTEXT_SYSTEM;
    $block->showinsubcontexts = 0;
    $block->pagetypepattern = '*';
    $block->subpagepattern = '*';
    $block->defaultregion = 'content';
    $block->defaultweight = 1;
    $block->configdata = '';

    $DB->insert_record('block_instances', $block);
}