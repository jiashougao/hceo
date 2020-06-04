<?php
namespace control\fields;

use think\Exception;
use think\Response;

class MobileCountryCode extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'class' => array(),
        'style' => array(),
        'placeholder' => '',
       // 'type' => 'select',
        'default'=>null,
        'value'=>'',
        'custom_attributes' => array ()
    );

    public function getOptions(){
        if(get_lang()==='zh-cn'){
            return [
                "+86"=>"+86 中国", "+1"=>"+1 美国本土外小岛屿","+1-242"=>"+1-242 巴哈马","+1-246"=>"+1-246 巴巴多斯","+1-264"=>"+1-264 安圭拉","+1-268"=>"+1-268 安提瓜和巴布达","+1-284"=>"+1-284 英属印度洋领地","+1-345"=>"+1-345 开曼群岛","+1-441"=>"+1-441 百慕大","+1-473"=>"+1-473 格林纳达","+1-649"=>"+1-649 特克斯和凯科斯群岛","+1-664"=>"+1-664 蒙特塞拉特","+1-671"=>"+1-671 关岛","+1-767"=>"+1-767 多米尼加","+1-787"=>"+1-787 波多黎各","+1-809"=>"+1-809 多米尼加共和国","+1-868"=>"+1-868 特立尼达和多巴哥","+1-876"=>"+1-876 牙买加","+1284"=>"+1284 维尔京群岛（英国）","+1340"=>"+1340 维尔京群岛（美国）","+1670"=>"+1670 北马里亚纳群岛","+20"=>"+20 埃及","+212"=>"+212 摩洛哥","+213"=>"+213 阿尔及利亚","+216"=>"+216 突尼斯","+218"=>"+218 阿拉伯利比亚民众国","+220"=>"+220 冈比亚","+221"=>"+221 塞内加尔","+222"=>"+222 毛里塔尼亚","+223"=>"+223 马里","+224"=>"+224 几内亚","+225"=>"+225 科特迪瓦","+226"=>"+226 布基纳法索","+227"=>"+227 尼日尔","+228"=>"+228 多哥","+229"=>"+229 贝宁","+230"=>"+230 毛里求斯","+231"=>"+231 利比里亚","+232"=>"+232 塞拉利昂","+233"=>"+233 加纳","+234"=>"+234 尼日利亚","+235"=>"+235 乍得","+236"=>"+236 中非共和国","+237"=>"+237 喀麦隆","+238"=>"+238 佛得角","+239"=>"+239 圣多美和普林西比","+240"=>"+240 赤道几内亚","+241"=>"+241 加蓬","+242"=>"+242 刚果","+243"=>"+243 刚果，刚果民主共和国","+244"=>"+244 安哥拉","+245"=>"+245 几内亚比绍","+247"=>"+247 阿森松岛","+248"=>"+248 塞舌尔","+249"=>"+249 苏丹","+250"=>"+250 卢旺达","+251"=>"+251 埃塞俄比亚","+252"=>"+252 索马里","+253"=>"+253 吉布提","+254"=>"+254 肯尼亚","+255"=>"+255 桑给巴尔","+256"=>"+256 乌干达","+257"=>"+257 布隆迪","+258"=>"+258 莫桑比克","+260"=>"+260 赞比亚","+261"=>"+261 马达加斯加","+262"=>"+262 留尼旺岛","+263"=>"+263 津巴布韦","+264"=>"+264 纳米比亚","+265"=>"+265 马拉维","+266"=>"+266 莱索托","+267"=>"+267 博茨瓦纳","+268"=>"+268 斯威士兰","+269"=>"+269 马约特","+27"=>"+27 南非","+290"=>"+290 圣赫勒拿","+291"=>"+291 厄立特里亚","+297"=>"+297 阿鲁巴","+298"=>"+298 法罗群岛","+299"=>"+299 格陵兰","+30"=>"+30 希腊","+31"=>"+31 荷兰","+32"=>"+32 比利时","+33"=>"+33 法国南部领土","+34"=>"+34 西班牙","+350"=>"+350 直布罗陀","+351"=>"+351 葡萄牙","+352"=>"+352 卢森堡","+353"=>"+353 爱尔兰","+354"=>"+354 冰岛","+355"=>"+355 阿尔巴尼亚","+356"=>"+356 马耳他","+357"=>"+357 塞浦路斯","+358"=>"+358 芬兰","+359"=>"+359 保加利亚","+36"=>"+36 匈牙利","+370"=>"+370 立陶宛","+371"=>"+371 拉脱维亚","+372"=>"+372 爱沙尼亚","+373"=>"+373 摩尔多瓦","+374"=>"+374 亚美尼亚","+375"=>"+375 白俄罗斯","+376"=>"+376 安道​​尔","+377"=>"+377 摩纳哥","+378"=>"+378 圣马力诺","+380"=>"+380 乌克兰","+381"=>"+381 南斯拉夫","+382"=>"+382 黑山","+385"=>"+385 克罗地亚","+386"=>"+386 斯洛文尼亚","+387"=>"+387 波斯尼亚和黑塞哥维那","+389"=>"+389 马其顿","+39"=>"+39 梵蒂冈城国（教廷）","+40"=>"+40 罗马尼亚","+41"=>"+41 瑞士","+420"=>"+420 捷克共和国","+421"=>"+421 斯洛伐克（斯洛伐克共和国）","+423"=>"+423 列支敦士登","+43"=>"+43 奥地利","+44"=>"+44 英国","+45"=>"+45 丹麦","+46"=>"+46 瑞典","+47"=>"+47 斯瓦尔巴群岛和扬马延","+48"=>"+48 波兰","+49"=>"+49 德国","+493"=>"+493 奥尔德尼岛","+500"=>"+500 福克兰群岛（马尔维纳斯）","+501"=>"+501 伯利兹","+502"=>"+502 危地马拉","+503"=>"+503 萨尔瓦多","+504"=>"+504 洪都拉斯","+505"=>"+505 尼加拉瓜","+506"=>"+506 哥斯达黎加","+507"=>"+507 巴拿马","+508"=>"+508 圣皮埃尔和密克隆岛","+509"=>"+509 海地","+51"=>"+51 秘鲁","+52"=>"+52 墨西哥","+53"=>"+53 古巴","+54"=>"+54 阿根廷","+55"=>"+55 巴西","+56"=>"+56 智利","+57"=>"+57 哥伦比亚","+58"=>"+58 委内瑞拉","+590"=>"+590 圣马丁","+591"=>"+591 玻利维亚","+592"=>"+592 圭亚那","+593"=>"+593 厄瓜多尔","+594"=>"+594 法属圭亚那","+595"=>"+595 巴拉圭","+596"=>"+596 马提尼克","+597"=>"+597 苏里南","+598"=>"+598 乌拉圭","+599"=>"+599 荷属安的列斯","+60"=>"+60 马来西亚","+61"=>"+61 赫德和麦克唐纳群岛","+62"=>"+62 印尼","+63"=>"+63 菲律宾","+64"=>"+64 新西兰","+65"=>"+65 新加坡的","+66"=>"+66 泰国","+670"=>"+670 东帝汶","+672"=>"+672 诺福克岛","+673"=>"+673 文莱","+674"=>"+674 瑙鲁","+675"=>"+675 巴布亚新几内亚","+676"=>"+676 汤加","+677"=>"+677 所罗门群岛","+678"=>"+678 瓦努阿图","+679"=>"+679 斐济","+680"=>"+680 帕劳","+681"=>"+681 瓦利斯和富图纳群岛","+682"=>"+682 库克群岛","+683"=>"+683 纽埃","+684"=>"+684 美属萨摩亚","+685"=>"+685 西撒哈拉","+686"=>"+686 基里巴斯","+687"=>"+687 新喀里多尼亚","+688"=>"+688 图瓦卢","+689"=>"+689 法属波利尼西亚","+690"=>"+690 托克劳","+691"=>"+691 密克罗尼西亚","+692"=>"+692 马绍尔群岛","+7"=>"+7 俄罗斯联邦","+81"=>"+81 日本","+82"=>"+82 韩国","+84"=>"+84 越南","+850"=>"+850 朝鲜","+852"=>"+852 香港","+853"=>"+853 澳门","+855"=>"+855 柬埔寨","+856"=>"+856 老挝人民民主共和国","+872"=>"+872 皮特凯恩","+880"=>"+880 孟加拉国","+886"=>"+886 台湾","+90"=>"+90 土耳其","+91"=>"+91 印度","+92"=>"+92 巴基斯坦","+93"=>"+93 阿富汗","+94"=>"+94 斯里兰卡","+95"=>"+95 缅甸","+960"=>"+960 马尔代夫","+961"=>"+961 黎巴嫩","+962"=>"+962 约旦","+963"=>"+963 阿拉伯叙利亚共和国","+964"=>"+964 伊拉克","+965"=>"+965 科威特","+966"=>"+966 沙特阿拉伯","+967"=>"+967 也门","+968"=>"+968 阿曼","+970"=>"+970 巴勒斯坦","+971"=>"+971 阿拉伯联合酋长国","+972"=>"+972 以色列","+973"=>"+973 巴林","+974"=>"+974 卡塔尔","+975"=>"+975 不丹","+976"=>"+976 蒙古","+977"=>"+977 尼泊尔","+98"=>"+98 伊朗（伊斯兰共和国）","+992"=>"+992 塔吉克斯坦","+993"=>"+993 土库曼斯坦","+994"=>"+994 阿塞拜疆","+995"=>"+995 格鲁吉亚","+996"=>"+996 吉尔吉斯斯坦","+998"=>"+998 乌兹别克斯坦","+"=>"其他国家"
            ];
        }

        return [
            "+86" => "+86 China", "+1" => "+1 United States Minor Outlying Islands", "+1-242" => "+1-242 Bahamas", "+1-246" => "+1-246 Barbados", "+1-264" => "+1-264 Anguilla", "+1-268" => "+1-268 Antigua and Barbuda", "+1-284" => "+1-284 British Indian Ocean Territory", "+1-345" => "+1-345 Cayman Islands", "+1-441" => "+1-441 Bermuda", "+1-473" => "+1-473 Grenada", "+1-649" => "+1-649 Turks and Caicos Islands", "+1-664" => "+1-664 Montserrat", "+1-671" => "+1-671 Guam", "+1-767" => "+1-767 Dominica", "+1-787" => "+1-787 Puerto Rico", "+1-809" => "+1-809 Dominican Republic", "+1-868" => "+1-868 Trinidad and Tobago", "+1-876" => "+1-876 Jamaica", "+1284" => "+1284 Virgin Islands (British)", "+1340" => "+1340 Virgin Islands (U.S.)", "+1670" => "+1670 Northern Mariana Islands", "+20" => "+20 Egypt", "+212" => "+212 Morocco", "+213" => "+213 Algeria", "+216" => "+216 Tunisia", "+218" => "+218 Libya", "+220" => "+220 Gambia", "+221" => "+221 Senegal", "+222" => "+222 Mauritania", "+223" => "+223 Mali", "+224" => "+224 Guinea", "+225" => "+225 Cote D'Ivoire", "+226" => "+226 Burkina Faso", "+227" => "+227 Niger", "+228" => "+228 Togo", "+229" => "+229 Benin", "+230" => "+230 Mauritius", "+231" => "+231 Liberia", "+232" => "+232 Sierra Leone", "+233" => "+233 Ghana", "+234" => "+234 Nigeria", "+235" => "+235 Chad", "+236" => "+236 Central African Republic", "+237" => "+237 Cameroon", "+238" => "+238 Cape Verde", "+239" => "+239 Sao Tome and Principe", "+240" => "+240 Equatorial Guinea", "+241" => "+241 Gabon", "+242" => "+242 Congo, The Republic of Congo", "+243" => "+243 Congo, The Democratic Republic Of The", "+244" => "+244 Angola", "+245" => "+245 Guinea-Bissau", "+247" => "+247 Ascension Island", "+248" => "+248 Seychelles", "+249" => "+249 Sudan", "+250" => "+250 Rwanda", "+251" => "+251 Ethiopia", "+252" => "+252 Somalia", "+253" => "+253 Djibouti", "+254" => "+254 Kenya", "+255" => "+255 Zanzibar", "+256" => "+256 Uganda", "+257" => "+257 Burundi", "+258" => "+258 Mozambique", "+260" => "+260 Zambia", "+261" => "+261 Madagascar", "+262" => "+262 Reunion", "+263" => "+263 Zimbabwe", "+264" => "+264 Namibia", "+265" => "+265 Malawi", "+266" => "+266 Lesotho", "+267" => "+267 Botswana", "+268" => "+268 Swaziland", "+269" => "+269 Mayotte", "+27" => "+27 South Africa", "+290" => "+290 St. Helena", "+291" => "+291 Eritrea", "+297" => "+297 Aruba", "+298" => "+298 Faroe Islands", "+299" => "+299 Greenland", "+30" => "+30 Greece", "+31" => "+31 Netherlands", "+32" => "+32 Belgium", "+33" => "+33 French Southern Territories", "+34" => "+34 Spain", "+350" => "+350 Gibraltar", "+351" => "+351 Portugal", "+352" => "+352 Luxembourg", "+353" => "+353 Ireland", "+354" => "+354 Iceland", "+355" => "+355 Albania", "+356" => "+356 Malta", "+357" => "+357 Cyprus", "+358" => "+358 Finland", "+359" => "+359 Bulgaria", "+36" => "+36 Hungary", "+370" => "+370 Lithuania", "+371" => "+371 Latvia", "+372" => "+372 Estonia", "+373" => "+373 Moldova", "+374" => "+374 Armenia", "+375" => "+375 Belarus", "+376" => "+376 Andorra", "+377" => "+377 Monaco", "+378" => "+378 San Marino", "+380" => "+380 Ukraine", "+381" => "+381 Yugoslavia", "+382" => "+382 Montenegro", "+385" => "+385 Croatia (local name=> Hrvatska)", "+386" => "+386 Slovenia", "+387" => "+387 Bosnia and Herzegovina", "+389" => "+389 Macedonia", "+39" => "+39 Vatican City State (Holy See)", "+40" => "+40 Romania", "+41" => "+41 Switzerland", "+420" => "+420 Czech Republic", "+421" => "+421 Slovakia (Slovak Republic)", "+423" => "+423 Liechtenstein", "+43" => "+43 Austria", "+44" => "+44 United Kingdom", "+45" => "+45 Denmark", "+46" => "+46 Sweden", "+47" => "+47 Svalbard and Jan Mayen Islands", "+48" => "+48 Poland", "+49" => "+49 Germany", "+493" => "+493 Alderney", "+500" => "+500 Falkland Islands (Malvinas)", "+501" => "+501 Belize", "+502" => "+502 Guatemala", "+503" => "+503 El Salvador", "+504" => "+504 Honduras", "+505" => "+505 Nicaragua", "+506" => "+506 Costa Rica", "+507" => "+507 Panama", "+508" => "+508 St. Pierre and Miquelon", "+509" => "+509 Haiti", "+51" => "+51 Peru", "+52" => "+52 Mexico", "+53" => "+53 Cuba", "+54" => "+54 Argentina", "+55" => "+55 Brazil", "+56" => "+56 Chile", "+57" => "+57 Colombia", "+58" => "+58 Venezuela", "+590" => "+590 Saint Martin", "+591" => "+591 Bolivia", "+592" => "+592 Guyana", "+593" => "+593 Ecuador", "+594" => "+594 French Guiana", "+595" => "+595 Paraguay", "+596" => "+596 Martinique", "+597" => "+597 Suriname", "+598" => "+598 Uruguay", "+599" => "+599 Netherlands Antilles", "+60" => "+60 Malaysia", "+61" => "+61 Heard and Mc Donald Islands", "+62" => "+62 Indonesia", "+63" => "+63 Philippines", "+64" => "+64 New Zealand", "+65" => "+65 Singapore", "+66" => "+66 Thailand", "+670" => "+670 Timor-Leste", "+672" => "+672 Norfolk Island", "+673" => "+673 Brunei Darussalam", "+674" => "+674 Nauru", "+675" => "+675 Papua New Guinea", "+676" => "+676 Tonga", "+677" => "+677 Solomon Islands", "+678" => "+678 Vanuatu", "+679" => "+679 Fiji", "+680" => "+680 Palau", "+681" => "+681 Wallis And Futuna Islands", "+682" => "+682 Cook Islands", "+683" => "+683 Niue", "+684" => "+684 American Samoa", "+685" => "+685 Western Sahara", "+686" => "+686 Kiribati", "+687" => "+687 New Caledonia", "+688" => "+688 Tuvalu", "+689" => "+689 French Polynesia", "+690" => "+690 Tokelau", "+691" => "+691 Micronesia", "+692" => "+692 Marshall Islands", "+7" => "+7 Russian Federation", "+81" => "+81 Japan", "+82" => "+82 Korea,the republic of", "+84" => "+84 Vietnam", "+850" => "+850 North Korea", "+852" => "+852 Hong Kong", "+853" => "+853 Macau", "+855" => "+855 Cambodia", "+856" => "+856 Lao People's Democratic Republic",  "+872" => "+872 Pitcairn", "+880" => "+880 Bangladesh", "+886" => "+886 Taiwan", "+90" => "+90 Turkey", "+91" => "+91 India", "+92" => "+92 Pakistan", "+93" => "+93 Afghanistan", "+94" => "+94 Sri Lanka", "+95" => "+95 Myanmar", "+960" => "+960 Maldives", "+961" => "+961 Lebanon", "+962" => "+962 Jordan", "+963" => "+963 Syrian Arab Republic", "+964" => "+964 Iraq", "+965" => "+965 Kuwait", "+966" => "+966 Saudi Arabia", "+967" => "+967 Yemen", "+968" => "+968 Oman", "+970" => "+970 Palestine", "+971" => "+971 United Arab Emirates", "+972" => "+972 Israel", "+973" => "+973 Bahrain", "+974" => "+974 Qatar", "+975" => "+975 Bhutan", "+976" => "+976 Mongolia", "+977" => "+977 Nepal", "+98" => "+98 Iran (Islamic Republic of)", "+992" => "+992 Tajikistan", "+993" => "+993 Turkmenistan", "+994" => "+994 Azerbaijan", "+995" => "+995 Georgia", "+996" => "+996 Kyrgyzstan", "+998" => "+998 Uzbekistan", "+" => "Other Country"
        ];
    }

    public function preview($strip_tags = false,$values=null){
       $value = $this->getValue();

        $options = $this->getOptions();

       $value = isset($options[$value])?$options[$value]:$value;
        if($strip_tags){
            return $value;
        }
        return "<div  class='form-control-textarea-preview'>{$value}</div>";
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );

        $options = $this->getOptions();

        ?>
        <select class="form-control <?php echo esc_attr(join(' ',array_unique($this->data['class']))  ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo  $this->data['disabled']?'disabled':''; ?>   <?php echo $this->getCustomAttributeHtml(  ); ?> >
            <?php if($options){
                foreach($options as $k=>$v){
                    ?><option value="<?php echo esc_attr($k);?>" <?php echo $k==$this->getValue()?'selected':''?>><?php echo $v;?></option><?php
                }
            }?>
        </select>

        <script type="text/javascript">
            (function($,undefined){
               $(function(){
                   $('#<?php echo $field?>').change(function(){
                       $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                           column:"<?php echo $field?>",
                           event:'keyup',
                           value:$('#<?php echo $field?>').val()
                       });
                   }).focus(function(){
                       $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                           column:"<?php echo $field?>",
                           event:'focus',
                           value:$('#<?php echo $field?>').val()
                       });
                   }).blur(function(){
                       $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                           column:"<?php echo $field?>",
                           event:'blur',
                           value:$('#<?php echo $field?>').val()
                       });
                   });
                   $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                       form.<?php echo esc_attr($this->key)?> = $('#<?php echo $field?>').val();
                   });
                   window.set_field_<?php echo $field?>_value = function(value){
                       $('#<?php echo $field?>').val(value).trigger('change');
                   };
               });
            })(jQuery);
        </script>
        <?php
    }

    public function param(&$res,$call=null){
        $response = parent::param($res,$call);
        $options = $this->getOptions();

        if(!is_null_or_empty($response)){
            if(!isset($options[$response])){
                throw new Exception(errorMessage(10002,array($this->data['title'])),10002);
            }
        }


        return $response;
    }
}