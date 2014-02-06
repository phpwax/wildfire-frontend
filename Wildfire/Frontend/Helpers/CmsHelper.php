<?php
namespace Wildfire\Frontend\Helpers;

class CmsHelper extends \Twig_Extension{

  public function getFilters() {
    return [
        new \Twig_SimpleFilter('render_media',        [$this, 'render_media']),
        new \Twig_SimpleFilter('format_cms_content',  [$this, 'format_cms_content'],['is_safe' => ['html']])
    ];
  }

  public function render_media($image, $size) {
    $ext = pathinfo($image["source"], PATHINFO_EXTENSION);
    return "/m/".substr($image["hash"],0,6)."/".$size.".".$ext;
  }

  public function format_cms_content($string) {
    return \CmsTextFilter::filter("before_output",$string);
  }

  public function getName(){
    return 'cms_helper_extension';
  }

}