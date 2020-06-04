<?php
namespace control\fields;

use org\helper\StringHelper;
use think\Exception;
use think\Response;

/**
 * 表单字段类型
 * Class Base
 * @since v1.0.0
 * @author ranj
 * @package control\fields
 */
abstract class Base
{
    /**
     * 字段名
     * @var string
     */
    protected $key;

    /**
     * 字段配置
     * @var array
     */
    protected $data;

    /**
     * 字段默认配置
     * @var array
     */
    protected $default = array(
        /**
         * 字段标题
         * @var string
         */
        'title'=>'',

        /**
         * 声明字段是否必填
         * @var boolean
         */
        'required'=>false,

        /**
         * 字段默认值
         * @var mixed
         */
        'default'=>'',

        /**
         * 字段值
         * @var mixed
         */
        'value'=>'',

        /**
         * 字段排序(数据列表时使用)
         * 越大越靠后
         * @var integer
         */
        'sort'=>100,

        /**
         * 自定义数据验证(指定一个function)
         *
         * @var callable
         * function($field/*字段, $value/*字段的值){ throw new Exception('some error.',500)}
         */
        'validate'=>null,

        /**
         * 自定义数据验证(指定一个function)
         *
         * @var string|callable
         * function($entity/*当前表单entity){ echo '<div>test</div>';}
         */
        'type'  =>null,

        /**
         * 自定义数据验证(指定一个function)
         *
         * @var callable
         * function($entity/*当前表单entity,$strip_tags/*是否清除html){ return '<div>test</div>';}
         */
        'preview'  =>null,

        /**
         * 数据列表页面：字段开启排序功能
         *
         * @var boolean
         */
        'sortable'=>false,

        /**
         * 数据列表页面：字段开启检索功能
         *
         * @var boolean
         */
        'searchable'=>false
    );


    /**
     * 表单ID
     *
     * @var string
     */
    protected $form_id;

    public function __construct($key,$data){
        $this->key =esc_attr( $key);
        $this->data = parse_args($data,$this->default);
    }

    /**
     * 获取字段名
     *
     * @return string
     */
    public function getColumnName(){
        return $this->key;
    }

    /**
     * 获取字段的配置信息
     *
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function get($key,$default=null){
        return isset($this->data[$key])?$this->data[$key]:$default;
    }

    /**
     * 生成字段HTML
     *
     * @param string $form_id
     * @return mixed
     */
    abstract function generateField($form_id);

    /**
     * 获取表单下字段唯一ID
     *
     * @param string $form_id
     * @return string
     */
    protected function getFieldKey($form_id) {
        return "{$form_id}_{$this->key}";
    }

    /**
     * style 数组 =>string
     * 数组结构的样式配置，转换为字符串
     *
     * @param array $style
     * @return string
     */
    public static function customerStyleHtml($style){
        if(!$style||!is_array($style)){
            return "";
        }

        $s="";
        foreach ($style as $k=>$v){
            if($s){$s.=";";}
            $s.=esc_attr($k).":".esc_attr($v);
        }
        return $s;
    }

    /**
     * 获取自定义样式HTML
     *
     * @return string
     */
    protected function getCustomerStyleHtml(){
        $style = isset($this->data['style'])?$this->data['style']:array();
        return self::customerStyleHtml($style);
    }

    /**
     * 获取自定义attribute HTML
     *
     * @return string
     */
    protected function getCustomAttributeHtml() {
        $data = $this->data;
        $custom_attributes = array ();

        if (! empty ( $data ['custom_attributes'] ) && is_array ( $data ['custom_attributes'] )) {

            foreach ( $data ['custom_attributes'] as $attribute => $attribute_value ) {
                $custom_attributes [] = esc_attr ( $attribute ) . '="' . esc_attr ( $attribute_value ) . '"';
            }
        }

        return implode ( ' ', $custom_attributes );
    }

    /**
     * 获取字段的值
     *
     * @param bool $format 是否把字符串数值转换为数组等原生格式
     * @return mixed
     */
    protected function getValue($format=false){
        return is_null($this->data['value'])||$this->data['value']===''?(isset($this->data['default'])?$this->data['default']:null):$this->data['value'];
    }

    /**
     * 字段默认通用验证函数
     *
     * @param mixed $value 未验证的字段值
     * @return mixed 已验证的字段值
     * @throws Exception
     */
    final protected function validate($value){
        if(isset($this->data['required'])&&$this->data['required']){
            if(is_null($value)||$value===''){
                throw new Exception(errorMessage(10000,array($this->data['title'])),10000);
            }else if(is_string($value)&&is_null_or_whitespace($value)){
                throw new Exception(errorMessage(10000,array($this->data['title'])),10000);
            }
        }

        if(!empty($this->data['validate'])){
            if(is_string($this->data['validate'])){
                $position = strpos($this->data['validate'],'=');
                if($position!==false){
                    $validators = array(
                        substr($this->data['validate'],0,$position),
                        substr($this->data['validate'],$position+1)
                    );

                    $res =  call_user_func_array($validators[0],array($this->data,$value,$validators[2]));
                    if($res instanceof Response){
                        return $res;
                    }
                    return $value;
                }
            }

            $res =  call_user_func_array($this->data['validate'],array($this->data,$value));
            if($res instanceof Response){
                return $res;
            }
        }
        return $value;
    }

    /**
     *
     * 从call获取字段的值，并且验证,丢入$res中
     *
     * @param array $res 字段值列表  [key1=>value1,key2=>value2]
     * @param callable $call 字段值获取来源
     * @return mixed 返回已验证过的值
     * @throws Exception
     */
    public function param(&$res,$call=null){
        if(!$call){
            $call = function($key){
                return request()->param($key);
            };
        }

        $res[$this->key] = call_user_func($call,$this->key);
        return $this->validate($res[$this->key]);
    }


    /**
     * 获取字段的默认值(表单未填写时，默认的数值)
     *
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getDefault($default=null){
        return isset($this->data['default'])?$this->data['default']:$default;
    }

    /**
     * 字段绑定值
     *
     * 预览数据前，需要给表单绑定value值
     *
     * @param array $field 当前字段的描述信息
     * @param array $values 表单数据集合
     */
    public function setValue(&$field,$values){
        $field['value'] = isset($values[$this->key])?$values[$this->key]:$this->getDefault();
    }

    /**
     * 数据预览HTML输出
     *
     * 预览场景：数据列表，excel数据导出导出
     *
     * @param bool $strip_tags 是否去除html标签(excel导出时，需要去除html，保留原有数值)
     * @return string
     */
    public function preview($strip_tags=false,$values=null){
        $value = $this->getValue();
        if($strip_tags){
            return strip_all_tags($value,true);
        }

        return $value;
    }

}