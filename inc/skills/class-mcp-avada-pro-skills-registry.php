<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'interface-mcp-avada-pro-module.php';
require_once plugin_dir_path(__FILE__) . '../tools/class-mcp-avada-pro-rendered-css-variable-verifier.php';
require_once plugin_dir_path(__FILE__) . '../debug/class-mcp-avada-pro-responsive-drift-debugger.php';
require_once plugin_dir_path(__FILE__) . 'class-mcp-avada-pro-grid-normalization-pattern.php';
require_once plugin_dir_path(__FILE__) . '../tools/class-mcp-avada-pro-fusion-code-block-sanitizer.php';

class MCP_Avada_Pro_Skills_Registry
{
    /**
     * @var array<int, MCP_Avada_Pro_Module_Interface>
     */
    protected array $modules = array();

    /**
     * Bootstrap and register default modules.
     */
    public static function bootstrap(): void
    {
        $registry = new self();
        $registry->register_modules();
    }

    public function __construct(array $modules = array())
    {
        if (!empty($modules)) {
            $this->modules = $modules;
            return;
        }

        $this->modules = $this->get_default_modules();
    }

    /**
     * Register all modules from this registry.
     */
    public function register_modules(): void
    {
        foreach ($this->modules as $module) {
            if ($module instanceof MCP_Avada_Pro_Module_Interface) {
                $module->register();
            }
        }
    }

    /**
     * @return array<int, MCP_Avada_Pro_Module_Interface>
     */
    protected function get_default_modules(): array
    {
        $modules = array(
            new MCP_Avada_Pro_Rendered_CSS_Variable_Verifier(),
            new MCP_Avada_Pro_Responsive_Drift_Debugger(),
            new MCP_Avada_Pro_Grid_Normalization_Pattern(),
            new MCP_Avada_Pro_Fusion_Code_Block_Sanitizer(),
        );

        /**
         * Filter default skill/tool modules for mcp-avada-builder-pro.
         *
         * @param array<int, MCP_Avada_Pro_Module_Interface> $modules
         */
        $modules = apply_filters('mcp_avada_pro_skill_modules', $modules);

        // Keep only valid module types for predictable extensibility.
        return array_values(array_filter($modules, function ($module): bool {
            return $module instanceof MCP_Avada_Pro_Module_Interface;
        }));
    }
}

