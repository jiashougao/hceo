<?php
namespace org;

/**
 * 插件容器
 *
 * 主要功能：管理插件集合，执行相关事件
 *
 * @since v1.0.0
 * @author ranj
 * Class AddOnContainer
 * @package addon
 */
class AddOnContainer
{
    /**
     * @var AddOn[]
     */
    private $_addons = [];

    /**
     * 注册插件
     * @param AddOn $addon
     */
    public function register(AddOn $addon){
        $this->_addons[]=$addon;
    }

    public function init(){
        foreach ($this->_addons as $addon){
            if($addon->isActive()){
                $addon->onInit();
            }
        }
    }

    /**
     * 获取已安装的插件包
     * @return AddOn[]
     */
    public function getInstalledAddons(){
        return $this->_addons;
    }

    /**
     * 获取插件目录下的所有module
     */
    public function getModules(){
        $modules = array();
        foreach ($this->_addons as $addon){
            if($addon->isActive()){
                $module = $addon->getModule();
                if(empty($module)){
                    continue;
                }
                $modules[]=$module;
            }
        }
        return $modules;
    }
}