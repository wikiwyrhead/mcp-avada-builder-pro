<?php

if (!defined('ABSPATH')) {
    exit;
}

interface MCP_Avada_Pro_Module_Interface
{
    /**
     * Register hooks, abilities, and integrations for this module.
     */
    public function register(): void;
}

