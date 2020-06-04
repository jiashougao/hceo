<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/3/13
 * Time: 08:37
 */

namespace org\helper;


use Twig_Environment;
use Twig_Extension_Sandbox;
use Twig_Loader_Array;
use Twig_Sandbox_SecurityPolicy;
use Twig_SimpleFilter;

/**
 * Twig 模板语言编译器
 *
 * @since v1.0.0
 * @author ranj
 * Class TwigHelper
 * @package org\helper
 */
class TwigHelper
{
    private $policy;

    public function __construct()
    {
//        $tags = array(
//            'if',
//            'autoescape',
//            'filter',
//            'for',
//            'macro',
//            'set',
//            'spaceless',
//            'verbatim',
//
//        );
//        $filters = array(
//            'abs',
//            'batch',
//            'capitalize',
//            'convert_encoding',
//            'date',
//            'date_modify',
//            'default',
//            'escape',
//            'first',
//            'format',
//            'join',
//            'json_encode',
//            'keys',
//            'abs',
//        );
//        $methods = array(
//
//        );
//        $properties = array(
//
//        );
//        $functions = array('range');
//        $this->policy = new Twig_Sandbox_SecurityPolicy($tags, $filters, $methods, $properties, $functions);
    }

    public  function parse($template,$assigns,$filters=[]){
        $loader = new Twig_Loader_Array(array(
            'index.html' => $template,
        ));
        $twig = new Twig_Environment($loader);
        //$helper = new LiquidHelper($template['template']);
        foreach (TwigFilters::getFilters() as $filter){
            $filters[]=$filter;
        }
        foreach ($filters as $filter){
            $twig->addFilter($filter);
        }

        return $twig->render('index.html',$assigns);
    }
}